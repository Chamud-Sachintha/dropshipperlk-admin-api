<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'order_id',
        'type',                             // 1- transfer in    2- transfer out
        'product_id',
        'product_price',
        'resell_price',
        'quantity',
        'total_amount',
        'delivery_charge',
        'direct_commision',
        'team_commision',
        'profit',
        'profit_total',
        'create_time'
    ];

    public function add_log($info) {
        $map['reseller_id'] = $info['resellerId'];
        $map['order_id'] = $info['orderId'];

        if (array_key_exists('logType', $info)) {
            $map['type'] = $info['logType'];
        } else {
            $map['type'] = 1;
        }

        $map['product_id'] = $info['productId'];
        $map['product_price'] = $info['productPrice'];
        $map['resell_price'] = $info['resellPrice'];
        $map['quantity'] = $info['quantity'];
        $map['delivery_charge'] = $info['deliveryCharge'];
        $map['direct_commision'] = $info['directCommision'];
        $map['team_commision'] = $info['teamCommision'];
        $map['total_amount'] = $info['totalAmount'];
        $map['profit'] = $info['profit'];
        $map['profit_total'] = $info['profitTotal'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function get_total_earnings() {
        // $map['reseller_id'] = $seller;
        
        return $this->sum('profit_total');
    }

    public function get_log_by_seller($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->orderBy('create_time', 'desc')->get();
    }
}
