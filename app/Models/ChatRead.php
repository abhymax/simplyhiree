<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRead extends Model
{
    protected $table = 'chat_reads';

    protected $fillable = ['conversation_id', 'user_id', 'last_read_message_id', 'last_read_at'];

    protected $casts = ['last_read_at' => 'datetime'];
}
