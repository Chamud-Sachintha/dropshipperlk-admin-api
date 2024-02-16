<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_img',
        'create_time'
    ];

    public function add_log($info) {
        $map['banner_img'] = $info['bannerImage'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }
}
