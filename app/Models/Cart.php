<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'product_id',
        'create_time'
    ];

    public function add_log($info) {
        $map['seller_id'] = $info['sellerId'];
        $map['product_id'] = $info['productId'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }
}
