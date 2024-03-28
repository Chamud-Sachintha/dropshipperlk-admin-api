<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'status',
        'description',
        'create_time',
    ];

    public function add_log($categoryInfo) {
        $map['category_name'] = $categoryInfo['categoryName'];
        $map['status'] = $categoryInfo['status'];
        $map['description'] = $categoryInfo['description'];
        $map['create_time'] = $categoryInfo['createTime'];

        return $this->create($map);
    }

    public function find_by_name($categoryName) {
        $map['category_name'] = $categoryName;

        return $this->where($map)->first();
    }

    public function find_by_id($cid) {
        $map['id'] = $cid;

        return $this->where($map)->first();
    }

    public function find_all() {
        $map['status'] = 1;

        return $this->where($map)->get();
    }

    public function update_by_id($CategoryInfo) {
        $map1['id'] = $CategoryInfo['categoryid'];
        $map['category_name'] = $CategoryInfo['categoryName'];
        $map['description'] = $CategoryInfo['description'];
        $map['status'] = $CategoryInfo['status'];

        return $this->where($map1)->update($map);
    }
}
