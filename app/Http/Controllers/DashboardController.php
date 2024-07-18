<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\ProfitShare;
use Illuminate\Http\Request;
use App\Models\OrderEn;
use App\Models\Reseller;
use App\Models\KYCInformation;

class DashboardController extends Controller
{
    private $AppHelper;
    private $Orders;
    private $Reseller;
    private $ProfitShareLog;
    private $OrderEn;
    private $KYCModel;
    
    public function __construct()
    {   
        $this->AppHelper = new AppHelper();
        $this->Orders = new Order();
        $this->ProfitShareLog = new ProfitShare();
        $this->OrderEn = new OrderEn();
        $this->Reseller = new Reseller();
        $this->KYCModel = new KYCInformation();
    }

    public function getDashboardData(Request $request) {

        $dataList = array();

        $pending_orders = $this->OrderEn->get_pending_count_by_seller();
        $in_courier_orders = $this->OrderEn->get_in_courier_count_by_seller();
        $Complte_corders = $this->OrderEn->get_complete_count_by_seller();
        $delivered_corders = $this->OrderEn->get_pending_count();
        $total_orders = $this->OrderEn->get_total_orders();
        $cancle_orders = $this->OrderEn->get_camcle_count_by_seller();
        $paid_orders = $this->OrderEn->get_paid_order_count();
        $received_earnings = $this->ProfitShareLog->get_total_earnings();
        $pending_payment = $this->OrderEn->get_pending_payment();

        $TotalResellerCount = $this->Reseller->get_total_user_count();
        $PendingResellerCount = $this->KYCModel->get_pending_user_count();
        $ActiveResellerCount = $this->KYCModel->get_aproved_user_count();

        $returnOrders = $this->OrderEn->get_returned_order_count();
        $holdorder = $this->OrderEn->get_hold_order_count();

        $dataList['pendingOrderCount'] = $pending_orders;
        $dataList['inCourierOrderCount'] = $in_courier_orders;
        $dataList['completeOrderCount'] = $Complte_corders;
        $dataList['totalOrders'] = $total_orders;
        $dataList['cancleOrders'] = $cancle_orders;
        $dataList['paidOrders'] = $paid_orders;
        $dataList['totalEarnigs'] = $received_earnings;
        $dataList['pendingPayment'] = $pending_payment;
        $dataList['DeliverdPayment'] = $delivered_corders;
        $dataList['TotalResellerCount'] = $TotalResellerCount;
        $dataList['PendingResellerCount'] = $PendingResellerCount;
        $dataList['ActiveResellerCount'] = $ActiveResellerCount;
        $dataList['orderreturnedcount'] = $returnOrders;
        $dataList['orderholdcount'] = $holdorder;

        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);

    }
}
