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
        'payment_method',
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
}
