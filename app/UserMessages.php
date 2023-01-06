<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMessages extends Model
{
    public function message() {
        return $this->belongsTo(Messages::class);
    }
}
