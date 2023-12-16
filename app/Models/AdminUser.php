<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AdminUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'token',
        'login_time',
        'create_time'
    ];

    public function add_log($adminUserInfo) {
        $map['first_name'] = $adminUserInfo['firstName'];
        $map['last_name'] = $adminUserInfo['lastName'];
        $map['email'] = $adminUserInfo['email'];
        $map['password'] = Hash::make($adminUserInfo['password']);
        $map['create_time'] = $adminUserInfo['createTime'];

        return $this->create($map);
    }

    public function find_by_username($userName) {
        $map['email'] = $userName;

        return $this->where($map)->first();
    }

    public function update_login_token($uid, $tokenInfo) {
        $map['token'] = $tokenInfo['token'];
        $map['login_time'] = $tokenInfo['loginTime'];

        return $this->where(array('id' => $uid))->update($map);
    }

    public function find_by_token($token) {
        $map['token'] = $token;

        return $this->where($map)->first();
    }
}
