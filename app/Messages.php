<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    public function userMessages() {
        return $this->hasMany(UserMessages::class);
    }

    public function users () {
        return $this->belongsToMany(User::class, 'user_messages', 'message_id', 'sender_id')
            ->withTimestamps();
    }
}
