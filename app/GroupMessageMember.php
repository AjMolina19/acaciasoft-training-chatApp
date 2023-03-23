<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupMessageMember extends Model
{
    protected $fillable = ['user_id', 'group_message_id', 'status'];

    public function groupMessage()
    {
        return $this->belongsTo(GroupMessage::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
