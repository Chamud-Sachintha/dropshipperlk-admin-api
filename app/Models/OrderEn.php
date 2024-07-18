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
        $map1['courier_name'] = $info['courierName'];
        $map1['order_status'] = 4;

        return $this->where($map)->update($map1);
    }

    public function update_pay_status_by_order($info) {
        $map['id'] = $info['orderId'];
        $map1['payment_status'] = $info['paymentStatus'];

        return $this->where($map)->update($map1);
    }

    public function update_refund_by_order($info) {
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

    // DashBoard Count 
    public function get_pending_count_by_seller() {
        // $map['reseller_id'] = $seller;
        $map['order_status'] = 0;

        return $this->where($map)->count();
    }

    public function get_in_courier_count_by_seller() {
        // $map['reseller_id'] = $seller;
        $map['order_status'] = 4;

        return $this->where($map)->count();
    }

    public function get_complete_count_by_seller() {
        // $map['reseller_id'] = $seller;
        $map['order_status'] = 7;

        return $this->where($map)->count();
    }

    

    public function get_camcle_count_by_seller() {
        // $map['reseller_id'] = $seller;
        $map['order_status'] = 3;

        return $this->where($map)->count();
    }

    public function get_paid_order_count() {
        // $map['reseller_id'] = $seller;
        $map['payment_status'] = 1;

        return $this->where($map)->count();
    }

    public function get_total_orders() {
        // $map['reseller_id'] = $seller;

        return $this->count();
    }

    public function get_pending_payment() {
        // $map['reseller_id'] = $seller;
        $map['payment_status'] = 0;
        $map['order_status'] = 5;

        return $this->where($map)->sum("total_amount");
    }

    public function get_pending_count() {
        // $map['reseller_id'] = $seller;
        $map['order_status'] = 5;

        return $this->where($map)->count();
    }

    public function get_hold_order_count() {
        // $map['reseller_id'] = $seller;
        $map['order_status'] = 1;

        return $this->where($map)->count();
    }

    public function get_returned_order_count() {
        // $map['reseller_id'] = $seller;
        $map['order_status'] = 6;

        return $this->where($map)->count();
    }

}
