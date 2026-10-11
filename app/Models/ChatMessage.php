<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = ['conversation_id', 'sender_user_id', 'sender_side', 'body'];

    public function conversation() { return $this->belongsTo(ChatConversation::class, 'conversation_id'); }
    public function sender()       { return $this->belongsTo(User::class, 'sender_user_id'); }
    public function attachments()  { return $this->hasMany(ChatAttachment::class, 'message_id'); }
}
