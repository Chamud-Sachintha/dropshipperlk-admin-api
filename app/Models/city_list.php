<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class city_list extends Model
{
    use HasFactory;

    public function validate_city($city, $district) {
        $map['district'] = $district;
        $map['city'] = $city;

        return $this->where($map)->first();
    }
}
