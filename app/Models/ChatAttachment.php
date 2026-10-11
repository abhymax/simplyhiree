<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAttachment extends Model
{
    protected $table = 'chat_attachments';

    protected $fillable = ['message_id', 'path', 'original_name', 'mime', 'size'];

    public function message() { return $this->belongsTo(ChatMessage::class, 'message_id'); }

    public function url(): string
    {
        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($this->path, '/');
    }
}
