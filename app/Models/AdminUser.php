<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'token',
        'login_time',
        'create_time'
    ];

    public function find_by_username($userName) {
        $map['email'] = $userName;

        return $this->where($map)->first();
    }

    public function find_by_token($token) {
        $map['token'] = $token;

        return $this->where($map)->first();
    }
}
