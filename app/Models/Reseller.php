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

    public function find_by_id($sellerId) {
        $map['id'] = $sellerId;

        return $this->where($map)->first();
    }

    public function get_ref_list_by_seller($sellerRef) {
        $map['ref_code'] = $sellerRef;

        return $this->where($map)->get();
    }
}
