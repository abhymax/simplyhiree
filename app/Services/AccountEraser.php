<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Permanently erase an account and everything attached to it.
 *
 * Writes a JSON backup before touching anything, then deletes children before
 * parents inside a transaction. Used by the admin deletion-request screen so
 * this is a reviewed action in the product rather than a hand-run command.
 */
class AccountEraser
{
    /** @return array{ok:bool, message:string, backup:?string, counts:array} */
    public function erase(User $user): array
    {
        $uid = (int) $user->id;

        // Never let an admin account be erased through this path.
        if ($user->hasRole('Superadmin') || $user->hasRole('Manager')) {
            return ['ok' => false, 'message' => 'Administrator accounts cannot be erased here.', 'backup' => null, 'counts' => []];
        }

        $applicationIds = $this->applicationIdsFor($user);
        $candidateIds   = DB::table('candidates')->where('added_by_user_id', $uid)->pluck('id')->all();

        $backupPath = $this->writeBackup($user, $applicationIds);

        $counts = [];

        try {
            DB::transaction(function () use ($uid, $applicationIds, &$counts) {
                $sessionIds = DB::table('assessment_sessions')->whereIn('job_application_id', $applicationIds)->pluck('id')->all();
                $attemptIds = $sessionIds
                    ? DB::table('assessment_attempts')->whereIn('assessment_session_id', $sessionIds)->pluck('id')->all()
                    : [];

                if ($attemptIds) {
                    $counts['assessment_answers'] = DB::table('assessment_attempt_answers')->whereIn('assessment_attempt_id', $attemptIds)->delete();
                    $counts['assessment_attempts'] = DB::table('assessment_attempts')->whereIn('id', $attemptIds)->delete();
                }
                if ($sessionIds) {
                    $counts['assessment_sessions'] = DB::table('assessment_sessions')->whereIn('id', $sessionIds)->delete();
                }

                if ($applicationIds) {
                    $counts['interview_rounds']   = DB::table('interview_rounds')->whereIn('job_application_id', $applicationIds)->delete();
                    $counts['activities']         = DB::table('application_activities')->whereIn('job_application_id', $applicationIds)->delete();
                    $counts['offer_letters']      = DB::table('offer_letters')->whereIn('job_application_id', $applicationIds)->delete();
                    $counts['job_applications']   = DB::table('job_applications')->whereIn('id', $applicationIds)->delete();
                }

                // Chat: the person's own threads and anything they wrote.
                $convIds = DB::table('chat_conversations')->where('party_user_id', $uid)->pluck('id')->all();
                if ($convIds) {
                    $msgIds = DB::table('chat_messages')->whereIn('conversation_id', $convIds)->pluck('id')->all();
                    if ($msgIds) {
                        DB::table('chat_attachments')->whereIn('message_id', $msgIds)->delete();
                    }
                    DB::table('chat_messages')->whereIn('conversation_id', $convIds)->delete();
                    DB::table('chat_reads')->whereIn('conversation_id', $convIds)->delete();
                    $counts['chat_conversations'] = DB::table('chat_conversations')->whereIn('id', $convIds)->delete();
                }
                DB::table('chat_messages')->where('sender_user_id', $uid)->delete();
                DB::table('chat_reads')->where('user_id', $uid)->delete();

                $counts['profile']       = DB::table('user_profiles')->where('user_id', $uid)->delete();
                $counts['roles']         = DB::table('model_has_roles')->where('model_id', $uid)->delete();
                $counts['notifications'] = DB::table('notifications')->where('notifiable_id', $uid)->delete();
                $counts['user']          = DB::table('users')->where('id', $uid)->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Account erase failed for user ' . $uid . ': ' . $e->getMessage());

            return ['ok' => false, 'message' => 'Could not erase the account: ' . $e->getMessage(), 'backup' => $backupPath, 'counts' => []];
        }

        // Files last: if the transaction rolled back, nothing has been lost.
        foreach ($this->filePathsFor($uid, $candidateIds) as $path) {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                Log::warning('Could not remove file ' . $path . ': ' . $e->getMessage());
            }
        }

        return ['ok' => true, 'message' => 'Account permanently erased.', 'backup' => $backupPath, 'counts' => array_filter($counts)];
    }

    /** Everything that would be removed, for the confirmation screen. */
    public function preview(User $user): array
    {
        $uid = (int) $user->id;
        $applicationIds = $this->applicationIdsFor($user);

        return [
            'applications'    => count($applicationIds),
            'interviews'      => $applicationIds ? DB::table('interview_rounds')->whereIn('job_application_id', $applicationIds)->count() : 0,
            'assessments'     => $applicationIds ? DB::table('assessment_sessions')->whereIn('job_application_id', $applicationIds)->count() : 0,
            'offer_letters'   => $applicationIds ? DB::table('offer_letters')->whereIn('job_application_id', $applicationIds)->count() : 0,
            'conversations'   => DB::table('chat_conversations')->where('party_user_id', $uid)->count(),
            'files'           => count($this->filePathsFor($uid, DB::table('candidates')->where('added_by_user_id', $uid)->pluck('id')->all())),
        ];
    }

    private function applicationIdsFor(User $user): array
    {
        $uid = (int) $user->id;

        $ids = DB::table('job_applications')->where('candidate_user_id', $uid)->pluck('id')->all();

        // A candidate record may also be linked by email.
        $candidateIds = DB::table('candidates')->where('email', $user->email)->pluck('id')->all();
        if ($candidateIds) {
            $ids = array_merge($ids, DB::table('job_applications')->whereIn('candidate_id', $candidateIds)->pluck('id')->all());
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function filePathsFor(int $uid, array $candidateIds): array
    {
        $paths = [];

        $resume = DB::table('user_profiles')->where('user_id', $uid)->value('resume_path');
        if ($resume) {
            $paths[] = $resume;
        }
        if ($candidateIds) {
            foreach (DB::table('candidates')->whereIn('id', $candidateIds)->pluck('resume_path') as $p) {
                if ($p) {
                    $paths[] = $p;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    private function writeBackup(User $user, array $applicationIds): ?string
    {
        try {
            $payload = [
                'erased_at'    => now()->toDateTimeString(),
                'user'         => DB::table('users')->where('id', $user->id)->first(),
                'profile'      => DB::table('user_profiles')->where('user_id', $user->id)->get(),
                'roles'        => DB::table('model_has_roles')->where('model_id', $user->id)->get(),
                'applications' => $applicationIds ? DB::table('job_applications')->whereIn('id', $applicationIds)->get() : [],
            ];

            $path = 'account-erasures/user_' . $user->id . '_' . now()->format('Ymd_His') . '.json';
            Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT));

            return $path;
        } catch (\Throwable $e) {
            Log::warning('Could not write erasure backup: ' . $e->getMessage());

            return null;
        }
    }
}
