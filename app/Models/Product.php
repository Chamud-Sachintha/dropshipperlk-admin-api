<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'price',
        'category',
        'team_commision',
        'direct_commision',
        'is_store_pick',
        'waranty',
        'description',
        'supplier_name',
        'stock_count',
        'create_time'
    ];

    public function add_log($productInfo) {
        $map['product_name'] = $productInfo['productName'];
        $map['price'] = $productInfo['price'];
        $map['category'] = $productInfo['category'];
        $map['team_commision'] = $productInfo['team_commision'];
        $map['direct_commision'] = $productInfo['directCommision'];
        $map['is_store_pick'] = $productInfo['isStorePick'];
        $map['waranty'] = $productInfo['waranty'];
        $map['description'] = $productInfo['description'];
        $map['supplier_name'] = $productInfo['suppierName'];
        $map['stock_count'] = $productInfo['stockCount'];
        $map['create_time'] = $productInfo['createTime'];

        return $this->create($map);
    }
}
