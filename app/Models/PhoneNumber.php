<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    protected $connection = 'safe_mysql';
    protected $fillable = ['user_id', 'number'];

    public function user(): User
    {
        return User::where('id', $this->user_id)->first();
    }
}
