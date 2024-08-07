<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'b_name',
        'address',
        'phone_number',
        'nic_number',
        'email',
        'password',
        'token',
        'login_time',
        'ref_code',
        'profit_total',
        'code',
        'create_time'
    ];

    public function bankDetails()
    {
        return $this->hasOne(BankDetails::class);
    }

    public function find_by_id($sellerId) {
        $map['id'] = $sellerId;

        return $this->where($map)->first();
    }

    public function get_ref_list_by_seller($sellerRef) {
        $map['code'] = $sellerRef;

        return $this->where($map)->get();
    }

    public function set_profit_total($info) {
        $map['id'] = $info['resellerId'];
        $map1['profit_total'] = $info['profitTotal'];

        $resp = $this->where($map)->update($map1);

        if ($resp) {
            return $this->where($map)->first();
        } else {
            return null;
        }
    }

    public function find_all() {
        return $this->all();
    }

    public function find_by_token($token) {
        $map['token'] = $token;

        return $this->where($map)->first();
    }
    public function update_password($userId ,$userPass)
    {
        //$map['id'] = $userId;
        $map['password'] = $userPass;

        return $this->where(array('id' => $userId))->update($map);

    }

    public function get_total_user_count(){
        return $this->count();
    }

    
}
