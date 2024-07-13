<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InCourierDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'way_bill',
        'package_create_status',
        'create_time',
    ];

    public function add_log($details) {
        $map['order'] = $details['order'];
        $map['way_bill'] = $details['wayBillNo'];
        $map['package_create_status'] = $details['packageCreateStatus'];
        $map['create_time'] = $details['createTime'];

        return $this->create($map);
    }

    public function find_by_order_id($orderNumber) {
        $map['order'] = $orderNumber;
        return $this->where($map)->first();
    }

    public function delete_by_id($id) {
        $map['id'] = $id;
        return $this->where($map)->delete();
    }

    public function get_pending_list() {
        $map['package_create_status'] = 0;
        return $this->get();
    }

    public function update_package_create_status($info) {
        $map['order'] = $info['orderNumber'];
        $map1['package_create_status'] = $info['status'];

        return $this->where($map)->update($map1);
    }
}
