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
        'images',
        'weight',
        'create_time'
    ];

    public function add_log($productInfo) {
        $map['product_name'] = $productInfo['productName'];
        $map['price'] = $productInfo['price'];
        $map['category'] = $productInfo['category'];
        $map['team_commision'] = $productInfo['teamCommision'];
        $map['direct_commision'] = $productInfo['directCommision'];
        $map['is_store_pick'] = $productInfo['isStorePick'];
        $map['waranty'] = $productInfo['waranty'];
        $map['description'] = $productInfo['description'];
        $map['weight'] = $productInfo['weight'];
        $map['supplier_name'] = $productInfo['supplierName'];
        $map['stock_count'] = $productInfo['stockCount'];
        $map['images'] = $productInfo['images'];
        $map['create_time'] = $productInfo['createTime'];

        return $this->create($map);
    }

    public function update_by_id($productInfo) {
        $map1['id'] = $productInfo['productId'];
        $map['product_name'] = $productInfo['productName'];
        $map['price'] = $productInfo['price'];
        // $map['category'] = $productInfo['category'];
        // $map['team_commision'] = $productInfo['teamCommision'];
        // $map['direct_commision'] = $productInfo['directCommision'];
        // $map['is_store_pick'] = $productInfo['isStorePick'];
        // $map['waranty'] = $productInfo['waranty'];
        $map['description'] = $productInfo['description'];
        // $map['weight'] = $productInfo['weight'];
        // $map['supplier_name'] = $productInfo['supplierName'];
        $map['status'] = $productInfo['status'];

        return $this->where($map1)->update($map);
    }

    public function find_by_p_name($pname) {
        $map['product_name'] = $pname;

        return $this->where($map)->first();
    }

    public function find_by_id($pid) {
        $map['id'] = $pid;

        return $this->where($map)->first();
    }

    public function find_all() {
       return  $this->all();
    }
}
