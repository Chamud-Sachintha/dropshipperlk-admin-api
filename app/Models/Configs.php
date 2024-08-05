<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configs extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'create_time'
    ];

    public function add_log($info) {

        $map1['name'] = $info['configName'];
        $map['value'] = $info['configValue'];
        $map['create_time'] = $info['createTime'];

        $verify = $this->where($map1)->first();

        if ($verify) {
            return $this->where($map1)->update($map);
        } else {
            $map['name'] = $info['configName'];
            
            return $this->create($map);
        }
    }

    public function find_by_config($name) {
        $map['name'] = $name;

        return $this->where($map)->first();
    }
}
