<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\Product;
use App\Models\Reseller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $AppHelper;
    private $Order;
    private $Product;
    private $Reseller;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Product = new Product();
        $this->Reseller = new Reseller();
    }

    public function getAllOngoingOrderList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Order->get_all_uncomplete();

                $dataList = array();
                foreach ($resp as $key => $value) {

                    $reseller_info = $this->Reseller->find_by_id($value['reseller_id']);
                    $product_info = $this->Product->find_by_id($value['product_id']);

                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['order'] = $value['order'];
                    $dataList[$key]['productName'] = $product_info['product_name'];
                    $dataList[$key]['resellerName'] = $reseller_info['full_name'];
                    $dataList[$key]['customerName'] = $value['name'];
                    $dataList[$key]['quantity'] = $value['quantity'];
                    $dataList[$key]['totalAmount'] = $value['total_amount'];

                    if ($value['payment_status'] == 0) {
                        $dataList[$key]['paymentStatus'] = "Pending";
                    } else if ($value['payment_statis'] == 1) {
                        $dataList[$key]['paymentStatus'] = "Paid";
                    } else {
                        $dataList[$key]['paymentStatus'] = "Refunded";
                    }

                    if ($value['order_status'] == 0) {
                        $dataList[$key]['orderStatus'] = "Pending";
                    } else if ($value['order_status'] == 1) {
                        $dataList[$key]['orderStatus'] = "Hold";
                    } else if ($value['order_status'] == 2) {
                        $dataList[$key]['orderStatus'] = "Packaging";
                    } else if ($value['order_status'] == 3) {
                        $dataList[$key]['orderStatus'] = "Cancel";
                    } else if ($value['order_status'] == 4) {
                        $dataList[$key]['orderStatus'] = "In Courier";
                    } else {
                        $dataList[$key]['orderStatus'] = "Delivered";
                    }
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getOrderInfoByOrderId(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderId = (is_null($request->orderId) || empty($request->orderId)) ? "" : $request->orderId;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Id is required.");
        } else {

            try {
                $order_info = $this->Order->find_by_order_id($orderId);

                if ($order_info) {
                    $product_info = $this->Product->find_by_id($order_info->product_id);

                    $dataList = array();
                    $dataList['productName'] = $product_info->product_name;

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
