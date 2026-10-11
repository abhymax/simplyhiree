<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ChatConversation extends Model
{
    protected $table = 'chat_conversations';

    protected $fillable = [
        'subject', 'party_type', 'party_user_id', 'job_id', 'job_application_id',
        'assigned_to_user_id', 'status', 'last_message_at', 'created_by',
    ];

    protected $casts = ['last_message_at' => 'datetime'];

    public const PARTY_TYPES = ['client' => 'Client', 'vendor' => 'Vendor', 'candidate' => 'Candidate'];

    public function messages()   { return $this->hasMany(ChatMessage::class, 'conversation_id'); }
    public function latestMessage() { return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany(); }
    public function party()      { return $this->belongsTo(User::class, 'party_user_id'); }
    public function assignee()   { return $this->belongsTo(User::class, 'assigned_to_user_id'); }
    public function job()        { return $this->belongsTo(Job::class, 'job_id'); }
    public function reads()      { return $this->hasMany(ChatRead::class, 'conversation_id'); }

    /** Is this user on the SimplyHiree side of the table? */
    public static function isInternal(User $user): bool
    {
        return $user->hasRole('Superadmin') || $user->hasRole('Manager');
    }

    /**
     * Restrict to what this user may see.
     *
     * Superadmin sees every thread. A Manager sees only threads assigned to
     * them. An outside party sees their organisation's threads, scoped to the
     * account owner so a client's or vendor's team shares one thread.
     */
    public function scopeVisibleTo(Builder $q, User $user): Builder
    {
        if ($user->hasRole('Superadmin')) {
            return $q;
        }

        if ($user->hasRole('Manager')) {
            return $q->where('assigned_to_user_id', $user->id);
        }

        if ($user->hasRole('client')) {
            return $q->where('party_type', 'client')->where('party_user_id', (int) $user->clientOwnerId());
        }

        if ($user->hasRole('partner')) {
            return $q->where('party_type', 'vendor')->where('party_user_id', (int) $user->partnerOwnerId());
        }

        // Candidates: only those with their own login reach chat at all.
        return $q->where('party_type', 'candidate')->where('party_user_id', $user->id);
    }

    /**
     * Messages this user has not yet read.
     *
     * Compared by message id rather than timestamp: timestamps are only
     * second-precision, so a reply arriving in the same second as the last
     * read was wrongly treated as already seen.
     */
    public function unreadCountFor(User $user): int
    {
        $lastRead = (int) ($this->reads()->where('user_id', $user->id)->value('last_read_message_id') ?? 0);

        return $this->messages()
            ->where('sender_user_id', '!=', $user->id)
            ->where('id', '>', $lastRead)
            ->count();
    }

    public function markReadFor(User $user): void
    {
        $latestId = (int) ($this->messages()->max('id') ?? 0);

        ChatRead::updateOrCreate(
            ['conversation_id' => $this->id, 'user_id' => $user->id],
            ['last_read_message_id' => $latestId, 'last_read_at' => now()]
        );
    }
}
