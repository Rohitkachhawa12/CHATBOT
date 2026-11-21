<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $primaryKey = 'conversation_id';

    protected $fillable = [
        'user_id',
        'conversation_token',
        'title',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
