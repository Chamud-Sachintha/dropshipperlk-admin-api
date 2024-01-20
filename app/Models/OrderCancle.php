<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCancle extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reseller',
        'total_amount',
        'status',               //0- pending 1- approve
        'create_time'
    ];

    public function update_refund_by_order($info) {
        $map['order_id'] = $info['orderId'];
        $map1['status'] = $info['paymentStatus'];

        return $this->where($map)->update($map1);
    }
}
