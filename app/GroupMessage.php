<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    protected $fillable = ['user_id', 'name'];

    public function groupMessageMember()
    {
        return $this->hasMany(GroupMessageMember::class);
    }
    public function userMessages()
    {
        return $this->hasMany(UserMessages::class);
    }
}
