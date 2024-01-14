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
                    } else if ($value['payment_status'] == 1) {
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
                    $dataList['totalAmount'] = $order_info->total_amount;
                    $dataList['quantity'] = $order_info->quantity;
                    $dataList['bankSlip'] = null;

                    if ($order_info->payment_method == 1) {
                        $dataList['paymentMethod'] = "Bank Deposit";
                        $dataList['bankSlip'] = $order_info['bank_slip'];
                    } else if ($order_info->payment_method == 2) {
                        $dataList['paymentMethod'] = "Cash On Delivery";
                    } else {
                        $dataList['paymentMethod'] = "KOKO Payment";
                    }

                    if ($order_info->payment_status == 0) {
                        $dataList['paymentStatus'] = "Pending";
                    } else {
                        $dataList['paymentStatus'] = "Paid";
                    }

                    if ($order_info['order_status'] == 0) {
                        $dataList['orderStatus'] = "Pending";
                    } else if ($order_info['order_status'] == 1) {
                        $dataList['orderStatus'] = "Hold";
                    } else if ($order_info['order_status'] == 2) {
                        $dataList['orderStatus'] = "Packaging";
                    } else if ($order_info['order_status'] == 3) {
                        $dataList['orderStatus'] = "Cancel";
                    } else if ($order_info['order_status'] == 4) {
                        $dataList['orderStatus'] = "In Courier";
                    } else {
                        $dataList['orderStatus'] = "Delivered";
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updatePaymentStatus(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderId = (is_null($request->orderId) || empty($request->orderId)) ? "" : $request->orderId;
        $paymentStatus = (is_null($request->paymentStatus) || empty($request->paymentStatus)) ? "" : $request->paymentStatus;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Id is required.");
        } else if ($paymentStatus == "") {
            return $this->AppHelper->responseMessageHandle(0, "Payment Status is required.");
        } else {

            try {
                $info = array();

                $info['orderId'] = $orderId;
                $info['paymentStatus'] = $paymentStatus;

                $resp = $this->Order->update_pay_status_by_order($info);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateOrderStatus(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderId = (is_null($request->orderId) || empty($request->orderId)) ? "" : $request->orderId;
        $orderStatus = (is_null($request->orderStatus) || empty($request->orderStatus)) ? "" : $request->orderStatus;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Id is required.");
        } else if ($orderStatus == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Status is required.");
        } else {

            try {
                $info = array();
                $info['orderId'] = $orderId;
                $info['orderStatus'] = $orderStatus;

                $resp = $this->Order->update_order_status_by_order($info);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateTrackingNumberOfOrder(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderId = (is_null($request->orderId) || empty($request->orderId)) ? "" : $request->orderId;
        $trackingNumber = (is_null($request->trackingNumber) || empty($request->trackingNumber)) ? "" : $request->trackingNumber;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Id is required.");
        } else if ($trackingNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tracking Number is required.");
        } else {

            try {
                $info = array();
                $info['orderId'] = $orderId;
                $info['trackingNumber'] = $trackingNumber;

                $resp = $this->Order->set_tracking_number_by_order($info);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
