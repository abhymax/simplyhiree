<?php

namespace App\Http\Controllers;

use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Chat between SimplyHiree and one outside party (client, vendor or
 * self-registered candidate). Outside parties never message each other, so a
 * conversation always has exactly two sides and needs no cross-party rules.
 */
class ChatController extends Controller
{
    private function user(): User
    {
        return Auth::user();
    }

    private function isInternal(): bool
    {
        return ChatConversation::isInternal($this->user());
    }

    /** Where this user's chat lives, so redirects work for every role. */
    private function homeRoute(): string
    {
        $u = $this->user();
        if ($this->isInternal())      return 'admin.chat.index';
        if ($u->hasRole('client'))    return 'client.chat.index';
        if ($u->hasRole('partner'))   return 'partner.chat.index';
        return 'candidate.chat.index';
    }

    /** Each role has its own layout, so pick the matching wrapper view. */
    private function view(string $name): string
    {
        $u = $this->user();
        $segment = $this->isInternal() ? 'admin'
            : ($u->hasRole('client') ? 'client'
            : ($u->hasRole('partner') ? 'partner' : 'candidate'));

        return "chat.{$segment}.{$name}";
    }

    public function index(Request $request)
    {
        $user = $this->user();

        $query = ChatConversation::query()
            ->visibleTo($user)
            ->with(['party', 'assignee', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        if ($request->filled('party_type') && $this->isInternal()) {
            $query->where('party_type', $request->input('party_type'));
        }
        if ($request->input('view') === 'archived') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('subject', 'like', "%{$s}%")
                  ->orWhereHas('party', fn ($p) => $p->where('name', 'like', "%{$s}%")
                                                     ->orWhere('email', 'like', "%{$s}%"));
            });
        }
        if ($request->input('mine') === '1' && $this->isInternal()) {
            $query->where('assigned_to_user_id', $user->id);
        }

        $conversations = $query->paginate(20)->withQueryString();

        $unread = [];
        foreach ($conversations as $c) {
            $unread[$c->id] = $c->unreadCountFor($user);
        }

        return view($this->view('index'), [
            'conversations' => $conversations,
            'unread'        => $unread,
            'isInternal'    => $this->isInternal(),
            'teamMembers'   => $this->isInternal()
                ? User::role('Manager')->orderBy('name')->get(['id', 'name'])
                : collect(),
            'partyOptions'  => $this->isInternal() ? $this->partyOptions() : [],
            'partyCounts'   => $this->isInternal() ? $this->partyCounts($user) : [],
            'homeRoute'     => $this->homeRoute(),
        ]);
    }

    /** How many chats sit under each tab. */
    private function partyCounts(User $user): array
    {
        return ChatConversation::query()
            ->visibleTo($user)
            ->selectRaw('party_type, COUNT(*) as c')
            ->groupBy('party_type')
            ->pluck('c', 'party_type')
            ->all();
    }

    /**
     * Clients, vendors and self-registered candidates SimplyHiree can write to.
     * Account owners only: a team member shares their organisation's thread.
     */
    private function partyOptions(): array
    {
        return User::query()
            ->whereNull('parent_partner_id')
            ->where(fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['client', 'partner', 'candidate'])))
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'email'])
            ->map(fn ($u) => [
                'id'    => $u->id,
                'label' => trim($u->name . ' — ' . $u->email),
            ])
            ->all();
    }

    public function show(ChatConversation $conversation)
    {
        $user = $this->user();
        $this->authorizeConversation($conversation);

        $conversation->load(['party', 'assignee', 'job']);
        $messages = $conversation->messages()
            ->with(['sender:id,name', 'attachments'])
            ->orderBy('id')
            ->get();

        $conversation->markReadFor($user);

        return view($this->view('show'), [
            'conversation' => $conversation,
            'messages'     => $messages,
            'isInternal'   => $this->isInternal(),
            'teamMembers'  => $this->isInternal()
                ? User::role('Manager')->orderBy('name')->get(['id', 'name'])
                : collect(),
            'homeRoute'    => $this->homeRoute(),
        ]);
    }

    /** Outside parties may open a thread any time; SimplyHiree picks the party. */
    public function store(Request $request)
    {
        $user = $this->user();

        $data = $request->validate([
            'subject'       => 'nullable|string|max:200',
            'body'          => 'required|string|max:5000',
            'party_type'    => 'nullable|in:client,vendor,candidate',
            'party_user_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($this->isInternal()) {
            if (empty($data['party_type']) || empty($data['party_user_id'])) {
                return back()->withInput()->with('error', 'Choose who this conversation is with.');
            }
            $partyType = $data['party_type'];
            $partyId   = (int) $data['party_user_id'];
        } else {
            [$partyType, $partyId] = $this->partyFor($user);
        }

        $conversation = ChatConversation::create([
            'subject'             => $data['subject'] ?? null,
            'party_type'          => $partyType,
            'party_user_id'       => $partyId,
            'assigned_to_user_id' => null,
            'status'              => 'open',
            'created_by'          => $user->id,
            'last_message_at'     => now(),
        ]);

        $message = $this->appendMessage($conversation, $data['body'], $request);

        if ($request->wantsJson()) {
            return response()->json([
                'ok'              => true,
                'conversation_id' => $conversation->id,
                'message'         => $this->messagePayload($message),
            ]);
        }

        return redirect()->route(str_replace('.index', '.show', $this->homeRoute()), $conversation)
            ->with('success', 'Conversation started.');
    }

    public function reply(Request $request, ChatConversation $conversation)
    {
        $this->authorizeConversation($conversation);

        $data = $request->validate([
            'body'          => 'nullable|string|max:5000',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp,txt,csv',
        ]);

        if (blank($data['body'] ?? null) && !$request->hasFile('attachments')) {
            return back()->with('error', 'Write a message or attach a file.');
        }

        if ($conversation->status === 'closed') {
            $conversation->update(['status' => 'open']);
        }

        $message = $this->appendMessage($conversation, $data['body'] ?? null, $request);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $this->messagePayload($message)]);
        }

        return back()->with('success', 'Message sent.');
    }

    /** Superadmin assigns a thread; a Manager then sees only what is theirs. */
    public function assign(Request $request, ChatConversation $conversation)
    {
        if (!$this->user()->hasRole('Superadmin')) {
            abort(403, 'Only a Superadmin can assign conversations.');
        }

        $data = $request->validate(['assigned_to_user_id' => 'nullable|integer|exists:users,id']);
        $conversation->update(['assigned_to_user_id' => $data['assigned_to_user_id'] ?: null]);

        $name = $conversation->fresh()->assignee?->name;

        return back()->with('success', $name ? "Assigned to {$name}." : 'Assignment cleared.');
    }

    public function toggleStatus(ChatConversation $conversation)
    {
        $this->authorizeConversation($conversation);
        if (!$this->isInternal()) {
            abort(403);
        }

        $conversation->update(['status' => $conversation->status === 'open' ? 'closed' : 'open']);

        return back()->with('success', 'Conversation marked ' . $conversation->fresh()->status . '.');
    }

    /** Shape one message for the JSON endpoints. */
    private function messagePayload(ChatMessage $m): array
    {
        return [
            'id'     => $m->id,
            'body'   => $m->body,
            'side'   => $m->sender_side,
            'sender' => $m->sender?->name ?? 'Unknown',
            'at'     => $m->created_at?->format('d M, h:i A'),
            'files'  => $m->attachments->map(fn ($a) => ['name' => $a->original_name, 'url' => $a->url()])->all(),
        ];
    }

    /**
     * Feeds the popup: the chats this user is handling, plus — for the
     * SimplyHiree side — the queue of unclaimed chats and their wait time.
     */
    public function threads(Request $request)
    {
        $user = $this->user();
        $internal = $this->isInternal();

        $shape = function (ChatConversation $c) use ($user, $internal) {
            return [
                'id'       => $c->id,
                'title'    => $internal ? ($c->party->name ?? 'Unknown') : ($c->subject ?: 'SimplyHiree'),
                'type'     => $c->party_type,
                'preview'  => \Illuminate\Support\Str::limit($c->latestMessage?->body ?? 'No messages yet', 40),
                'unread'   => $c->unreadCountFor($user),
                'when'     => $c->last_message_at?->diffForHumans(null, true),
                'waiting'  => $c->last_message_at ? $c->last_message_at->diffForHumans(null, true) : null,
                'assignee' => $c->assignee?->name,
            ];
        };

        $mineQuery = ChatConversation::query()
            ->visibleTo($user)
            ->with(['party:id,name', 'latestMessage', 'assignee:id,name'])
            ->whereNull('archived_at')
            ->when($internal, fn ($q) => $q->where('assigned_to_user_id', $user->id))
            ->orderByDesc('last_message_at')->orderByDesc('id')
            ->limit(20);

        $mine = $mineQuery->get()->map($shape);

        $queue = collect();
        $capacity = (int) ($user->chat_capacity ?: 5);
        $active = 0;

        if ($internal) {
            $active = ChatConversation::where('assigned_to_user_id', $user->id)
                ->where('status', 'open')->count();

            // Unclaimed and still open: oldest wait first, so nobody is forgotten.
            $queue = ChatConversation::query()
                ->whereNull('assigned_to_user_id')
                ->whereNull('archived_at')
                ->where('status', 'open')
                ->with(['party:id,name', 'latestMessage'])
                ->orderBy('last_message_at')
                ->limit(25)->get()->map($shape);
        }

        return response()->json([
            'threads'      => $mine,
            'queue'        => $queue,
            'unread_total' => $mine->sum('unread') + $queue->sum('unread'),
            'internal'     => $internal,
            'capacity'     => $capacity,
            'active'       => $active,
            'can_claim'    => $internal && $active < $capacity,
        ]);
    }

    /** Take a waiting chat, if under capacity. */
    public function claim(ChatConversation $conversation)
    {
        $user = $this->user();
        abort_unless($this->isInternal(), 403);

        $capacity = (int) ($user->chat_capacity ?: 5);
        $active = ChatConversation::where('assigned_to_user_id', $user->id)->where('status', 'open')->count();

        if ($conversation->assigned_to_user_id && (int) $conversation->assigned_to_user_id !== (int) $user->id) {
            $msg = 'Already taken by ' . ($conversation->assignee?->name ?? 'someone else') . '.';

            return request()->wantsJson()
                ? response()->json(['ok' => false, 'message' => $msg], 409)
                : back()->with('error', $msg);
        }

        if ($active >= $capacity && (int) $conversation->assigned_to_user_id !== (int) $user->id) {
            $msg = "You are handling {$active} of {$capacity} chats. Close or release one first.";

            return request()->wantsJson()
                ? response()->json(['ok' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $conversation->update(['assigned_to_user_id' => $user->id]);

        return request()->wantsJson()
            ? response()->json(['ok' => true, 'conversation_id' => $conversation->id])
            : back()->with('success', 'Chat claimed.');
    }

    /** Hand a chat back to the queue. */
    public function release(ChatConversation $conversation)
    {
        abort_unless($this->isInternal(), 403);
        $this->authorizeConversation($conversation);

        $conversation->update(['assigned_to_user_id' => null]);

        return request()->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Chat returned to the queue.');
    }

    /** How many chats this user will handle at once. */
    public function setCapacity(Request $request)
    {
        abort_unless($this->isInternal(), 403);

        $data = $request->validate(['capacity' => 'required|integer|min:1|max:20']);
        $user = $this->user();
        $user->forceFill(['chat_capacity' => $data['capacity']])->save();

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'capacity' => (int) $data['capacity']])
            : back()->with('success', 'Chat capacity updated.');
    }

    /** Move a chat out of the way without losing it. */
    public function archive(ChatConversation $conversation)
    {
        $this->authorizeConversation($conversation);
        abort_unless($this->isInternal(), 403);

        $conversation->update(['archived_at' => $conversation->archived_at ? null : now()]);

        return back()->with('success', $conversation->fresh()->archived_at
            ? 'Chat archived. Find it under Archived.'
            : 'Chat restored to the active list.');
    }

    /**
     * Delete a chat and its messages for good, including uploaded files.
     * Superadmin only: an archive is recoverable, this is not.
     */
    public function destroy(ChatConversation $conversation)
    {
        abort_unless($this->user()->hasRole('Superadmin'), 403, 'Only a Superadmin can delete a chat.');

        $messageIds = $conversation->messages()->pluck('id')->all();
        $paths = $messageIds
            ? \App\Models\ChatAttachment::whereIn('message_id', $messageIds)->pluck('path')->all()
            : [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($conversation, $messageIds) {
            if ($messageIds) {
                \App\Models\ChatAttachment::whereIn('message_id', $messageIds)->delete();
            }
            $conversation->messages()->delete();
            $conversation->reads()->delete();
            $conversation->delete();
        });

        // Files only after the rows are safely gone.
        foreach ($paths as $p) {
            try {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($p);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Could not remove chat file ' . $p . ': ' . $e->getMessage());
            }
        }

        return redirect()->route($this->homeRoute())->with('success', 'Chat deleted permanently.');
    }

    /** Poll endpoint: the thread refreshes without websockets. */
    public function poll(Request $request, ChatConversation $conversation)
    {
        $this->authorizeConversation($conversation);

        $after = (int) $request->input('after', 0);
        $messages = $conversation->messages()
            ->with(['sender:id,name', 'attachments'])
            ->where('id', '>', $after)
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'id'     => $m->id,
                'body'   => $m->body,
                'side'   => $m->sender_side,
                'sender' => $m->sender?->name ?? 'Unknown',
                'at'     => $m->created_at?->format('d M, h:i A'),
                'files'  => $m->attachments->map(fn ($a) => ['name' => $a->original_name, 'url' => $a->url()]),
            ]);

        if ($messages->isNotEmpty()) {
            $conversation->markReadFor($this->user());
        }

        return response()->json(['messages' => $messages]);
    }

    // ---------------------------------------------------------------

    private function authorizeConversation(ChatConversation $conversation): void
    {
        $visible = ChatConversation::query()
            ->whereKey($conversation->id)
            ->visibleTo($this->user())
            ->exists();

        abort_unless($visible, 403, 'You do not have access to this conversation.');
    }

    /** Which outside party this user represents. */
    private function partyFor(User $user): array
    {
        if ($user->hasRole('client'))  return ['client', (int) $user->clientOwnerId()];
        if ($user->hasRole('partner')) return ['vendor', (int) $user->partnerOwnerId()];
        return ['candidate', (int) $user->id];
    }

    private function appendMessage(ChatConversation $conversation, ?string $body, Request $request): ChatMessage
    {
        $user = $this->user();

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_user_id'  => $user->id,
            'sender_side'     => $this->isInternal() ? 'simplyhiree' : 'party',
            'body'            => $body,
        ]);

        foreach ((array) $request->file('attachments', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $path = $file->store('chat-attachments', 'public');
            ChatAttachment::create([
                'message_id'    => $message->id,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);
        }

        $conversation->update(['last_message_at' => now()]);
        $conversation->markReadFor($user);

        $this->notifyOtherSide($conversation, $message);

        return $message;
    }

    /** Email the other side so a message is not missed while they are away. */
    private function notifyOtherSide(ChatConversation $conversation, ChatMessage $message): void
    {
        try {
            $recipients = [];

            if ($message->sender_side === 'simplyhiree') {
                $party = $conversation->party;
                if ($party?->email) {
                    $recipients[] = $party->email;
                }
            } else {
                $assignee = $conversation->assignee;
                $recipients[] = $assignee?->email
                    ?: optional(User::role('Superadmin')->first())->email;
            }

            $recipients = array_filter(array_unique($recipients));
            if (empty($recipients)) {
                return;
            }

            $preview = \Illuminate\Support\Str::limit((string) $message->body, 300);
            $who     = $message->sender?->name ?? 'SimplyHiree';
            $subject = '[SimplyHiree] New message' . ($conversation->subject ? ': ' . $conversation->subject : '');

            Mail::raw(
                "{$who} sent you a message on SimplyHiree:\n\n{$preview}\n\nSign in to reply.",
                fn ($m) => $m->to($recipients)->subject($subject)
            );
        } catch (\Throwable $e) {
            Log::warning('Chat notification failed for conversation ' . $conversation->id . ': ' . $e->getMessage());
        }
    }
}
