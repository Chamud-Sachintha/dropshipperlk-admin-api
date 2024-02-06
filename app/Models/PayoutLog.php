<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'before_balance',
        'pay_out_amount',
        'current_balance',
        'create_time'
    ];

    public function add_log($info) {
        $map['reseller_id'] = $info['resellerId'];
        $map['before_balance'] = $info['beforeBalance'];
        $map['pay_out_amount'] = $info['payOutAmount'];
        $map['current_balance'] = $info['currentBalance'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function get_total_payout_by_seller($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->sum("pay_out_amount");
    }

    public function get_today_pay_amount($seller) {
        return $this
                ->where('reseller_id' ,'=', $seller)
                ->where('create_time' ,'>=', strtotime(date("Ymd")) - 86400)
                ->where('create_time' ,'<', strtotime(date("Ymd")) + 86400)
                ->sum("pay_out_amount");
    }

    public function find_all_by_seller($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->get();
    }
}
