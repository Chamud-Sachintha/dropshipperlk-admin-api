<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_img',
        'create_time',
        'status'
    ];

    public function add_log($info) {
      
    

   // dd($existingRows)
    $map['banner_img'] = $info['bannerImage'];
    $map['create_time'] = $info['createTime'];
    $map['status'] = 1; 

     return $this->create($map);
    }

    public function getbannerhistry(){
        $existingRows = $this->where('status', 1)->get();

    
        if ($existingRows->isNotEmpty()) {
            foreach ($existingRows as $existingRow) {
                $existingRow->update(['status' => 0]);
            }
        }
        return count($existingRows);
    }

    
    
}
