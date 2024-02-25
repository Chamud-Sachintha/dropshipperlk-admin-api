<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEn extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'order',
        'total_amount',
        'payment_method',
        'bank_slip',
        'payment_status',                       // 0- pending 1- paid  2- refund
        'order_status',                         // 0 - pending 1- hold 2- packaging 3- cancel 4- in courier 5- delivered
        'tracking_number',
        'is_reseller_completed',
        'create_time'
    ];

    public function get_all_orders() {
        return $this->all();
    }

    public function getOrderInfoByOrderNumber($orderNumber) {
        $map['order'] = $orderNumber;

        return $this->where($map)->first();
    }

    public function set_tracking_number_by_order($info) {
        $map['id'] = $info['orderId'];
        $map1['tracking_number'] = $info['trackingNumber'];

        return $this->where($map)->update($map1);
    }

    public function update_pay_status_by_order($info) {
        $map['id'] = $info['orderId'];
        $map1['payment_status'] = $info['paymentStatus'];

        return $this->where($map)->update($map1);
    }

    public function update_order_status_by_order($info) {
        $map['id'] = $info['orderId'];
        $map1['order_status'] = $info['orderStatus'];

        return $this->where($map)->update($map1);
    }

    public function get_by_id($id) {
        $map['id'] = $id;

        return $this->where($map)->first();
    }
}
