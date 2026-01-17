<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Models;
use Illuminate\Database\Eloquent\Model;

class UserMpin extends Model
{
    protected $fillable = ['user_id', 'mpin'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
