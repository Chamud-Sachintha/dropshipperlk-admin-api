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
        'order_status',                         // 0 - pending 1- hold 2- packaging 3- cancel 4- in courier 5- delivered 6- returned 7- complted
        'tracking_number',
        'is_reseller_completed',
        'create_time'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function orderEn()
    {
        return $this->hasOne(OrderEn::class, 'order', 'order');
    }

    public function get_all_uncomplete() {
        // $map['order_status'] = array('order_status', '!=', 5);

        // return $this->whereNotIn('order_status', [5])->get();

        return $this->all();
    }

    public function find_by_order_id($oid) {
        $map['id'] = $oid;

        return $this->where($map)->first();
    }

    public function get_order_by_order_number_new($orderNumber) {
        $map['order'] = $orderNumber;

        return $this->where($map)->get();
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

    public function update_refund_by_order($info) {
        $map['id'] = $info['orderId'];
        $map1['payment_status'] = $info['paymentStatus'];

        return $this->where($map)->update($map1);
    }

    public function get_order_count_by_seller($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->orderBy('create_time', 'desc')->count();
    }

    public function find_by_order_number($number) {
        $map['order'] = $number;

        return $this->where($map)->get();
    }


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

}
