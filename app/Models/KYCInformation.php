<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KYCInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'front_image_nic',
        'back_image_nic',
        'status',
        'create_time',
        'mod_time'
    ];

    public function get_all($status) {

        if ($status != "") {
            $map['status'] = $status;
            return $this->where($map)->get();
        } else {
            return $this->all();
        }
    }

    public function get_kyc_by_seller_id($sellerId) {
        $map['client_id'] = $sellerId;

        return $this->where($map)->first();
    }

    public function update_kyc_record($sellerId, $status) {
        $map['client_id'] = $sellerId;
        $map1['status'] = $status;

        return $this->where($map)->update($map1);
    }
}
