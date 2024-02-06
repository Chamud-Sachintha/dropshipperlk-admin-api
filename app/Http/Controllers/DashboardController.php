<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\ProfitShare;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $AppHelper;
    private $Orders;
    private $Reseller;
    private $ProfitShareLog;
    
    public function __construct()
    {   
        $this->AppHelper = new AppHelper();
        $this->Orders = new Order();
        $this->ProfitShareLog = new ProfitShare();
    }

    public function getDashboardData(Request $request) {

        $dataList = array();

        $pending_orders = $this->Orders->get_pending_count_by_seller();
        $in_courier_orders = $this->Orders->get_in_courier_count_by_seller();
        $delivered_corders = $this->Orders->get_complete_count_by_seller();
        $total_orders = $this->Orders->get_total_orders();
        $cancle_orders = $this->Orders->get_camcle_count_by_seller();
        $paid_orders = $this->Orders->get_paid_order_count();
        $received_earnings = $this->ProfitShareLog->get_total_earnings();
        $pending_payment = $this->Orders->get_pending_payment();

        $dataList['pendingOrderCount'] = $pending_orders;
        $dataList['inCourierOrderCount'] = $in_courier_orders;
        $dataList['completeOrderCount'] = $delivered_corders;
        $dataList['totalOrders'] = $total_orders;
        $dataList['cancleOrders'] = $cancle_orders;
        $dataList['paidOrders'] = $paid_orders;
        $dataList['totalEarnigs'] = $received_earnings;
        $dataList['pendingPayment'] = $pending_payment;

        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);

    }
}
