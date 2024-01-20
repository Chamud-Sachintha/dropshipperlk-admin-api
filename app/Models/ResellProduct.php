<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'product_id',
        'price',
        'status',
        'create_time'
    ];

    public function get_by_seller_and_pid($seller, $pid) {
        $map['reseller_id'] = $seller;
        $map['product_id'] = $pid;

        return $this->where($map)->first();
    }
}
