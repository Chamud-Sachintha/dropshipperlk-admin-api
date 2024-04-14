<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\OrderCancle;
use App\Models\Product;
use App\Models\Reseller;
use Illuminate\Http\Request;
use App\Models\OrderEn;

class OrderCancleController extends Controller
{
    private $AppHelper;
    private $Order;
    private $Product;
    private $Reseller;
    private $OrderCancleLog;
    private $OrderEn;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Product = new Product();
        $this->Reseller = new Reseller();
        $this->OrderCancleLog = new OrderCancle();
        $this->OrderEn = new OrderEn();
    }
    
    public function refundApprove(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderId = (is_null($request->orderId) || empty($request->orderId)) ? "" : $request->orderId;
        $status = (is_null($request->paymentStatus) || empty($request->paymentStatus)) ? "" : $request->paymentStatus;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order id is required.");
        } else if ($status == "") {
            return $this->AppHelper->responseMessageHandle(0, "Payment Status is required.");
        } else {

            try {
                $info = array();
                $info['orderId'] = $orderId;
                $info['paymentStatus'] = $status;

                $resp = $this->OrderCancleLog->update_refund_by_order($info);

                if ($orderId) {

                    $refund_info = array();
                    $refund_info['orderId'] = $orderId;
                    $refund_info['paymentStatus'] = '2';

                    $update_order = $this->OrderEn->update_refund_by_order($refund_info);

                    if ($update_order) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
