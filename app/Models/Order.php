<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'product_id',
        'order',
        'name',
        'address',
        'city',
        'district',
        'contact_1',
        'contact_2',
        'quantity',
        'total_amount',
        'payment_method',                       // 1- bank dep 2- cod 3- koko
        'payment_status',                       // 0- pending 1- paid
        'order_status',                         // 0 - pending 1- hold 2- packaging 3- cancel 4- in courier 5- delivered
        'tracking_number',
        'is_reseller_completed',
        'create_time'
    ];

    public function get_all_uncomplete() {
        $map['order_status'] = array('order_status', '!=', 5);

        return $this->where($map)->get();
    }

    public function find_by_order_id($oid) {
        $map['id'] = $oid;

        return $this->where($map)->first();
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

    public function set_tracking_number_by_order($info) {
        $map['id'] = $info['orderId'];
        $map1['tracking_number'] = $info['trackingNumber'];

        return $this->where($map)->update($map1);
    }
}
