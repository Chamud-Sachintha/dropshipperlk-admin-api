<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\city_list;
use App\Models\Order;
use App\Models\OrderCancle;
use App\Models\OrderEn;
use App\Models\Product;
use App\Models\ProfitShare;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Illuminate\Http\Request;
use DateTime;

class OrderController extends Controller
{
    private $AppHelper;
    private $Order;
    private $Product;
    private $Reseller;
    private $OrderCancleLog;
    private $ProfitShare;
    private $ResellProduct;
    private $OrderEn;
    private $City;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Product = new Product();
        $this->Reseller = new Reseller();
        $this->OrderCancleLog = new OrderCancle();
        $this->ProfitShare = new ProfitShare();
        $this->ResellProduct = new ResellProduct();
        $this->OrderEn = new OrderEn();
        $this->City = new city_list();
    }

    public function getAllOngoingOrderList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                // $resp = $this->Order->get_all_uncomplete();
                $resp = $this->OrderEn->get_all_orders();

                $dataList = array();
                foreach ($resp as $key => $value) {

                    $reseller_info = ($value['reseller_id'] != 0) ? $this->Reseller->find_by_id($value['reseller_id']) : ['full_name' => 'Direct Customer'];

                    // $product_info = $this->Product->find_by_id($value['product_id']);

                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['order'] = $value['order'];
                    // $dataList[$key]['productName'] = $product_info['product_name'];
                    if ($value['reseller_id'] == 0) {
                        $dataList[$key]['resellerName'] = 'Direct Customer';
                        $dataList[$key]['resellerReferral'] = '-';
                    } else {
                        $dataList[$key]['resellerName'] = $reseller_info['full_name'];
                        $dataList[$key]['resellerReferral'] = $reseller_info['code'];
                    }
                    // $dataList[$key]['customerName'] = $value['name'];
                    // $dataList[$key]['quantity'] = $value['quantity'];
                    $dataList[$key]['totalAmount'] = $value['total_amount'];

                    if($value['tracking_number'] == ""){
                        $dataList[$key]['trackingNumber'] = "-";
                    }
                    else{
                        $dataList[$key]['trackingNumber'] = $value['tracking_number'];
                    }

                    if($value['courier_name'] == ""){
                        $dataList[$key]['courierName'] = "-";
                    }
                    else{
                        $dataList[$key]['courierName'] = $value['courier_name'];
                    }

                    if($value['bank_slip'] == ""){
                        $dataList[$key]['bank_slip'] = "";
                    }
                    else{
                        $dataList[$key]['bank_slip'] = $value['bank_slip'];
                    }

                    if($value['payment_method'] == 1)
                    {
                        $dataList[$key]['paymentMethod'] = "Bank Deposit";
                    }
                    else if($value['payment_method'] == 2)
                    {
                        $dataList[$key]['paymentMethod'] = "Cash On Delivery";
                    }
                    else{
                        $dataList[$key]['paymentMethod'] = "KOKO Payment";
                    }
                    

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
                    } else if ($value['order_status'] == 5) {
                        $dataList[$key]['orderStatus'] = "Delivered";
                    }else if ($value['order_status'] == 7) {
                        $dataList[$key]['orderStatus'] = "Complete ";
                    } else if ($value['order_status'] == 8) {
                        $dataList[$key]['orderStatus'] = "Settled ";
                    } else if ($value['order_status'] == 9) {
                        $dataList[$key]['orderStatus'] = "Return Recieved ";
                    } else if ($value['order_status'] == 10) {
                        $dataList[$key]['orderStatus'] = "Ready to Change ";
                    } else if ($value['order_status'] == 11) {
                        $dataList[$key]['orderStatus'] = "Rescheduled ";
                    } else {
                        $dataList[$key]['orderStatus'] = "Return Order";
                    }
                    $dateTime = new DateTime($value['created_at']);
                    $formattedDate = $dateTime->format('Y-m-d');  
                    $dataList[$key]['orderPlaceDate'] = $formattedDate;
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
                    $dataList['orderCancled'] = 0;
                    $dataList['refundNotice'] = 0;
                    $dataList['images'] = json_decode($product_info['images']);

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
                    } else if ($order_info->payment_status == 1) {
                        $dataList['paymentStatus'] = "Paid";
                    } else {
                        $dataList['paymentStatus'] = "Refunded";
                    }

                    if ($order_info['order_status'] == 0) {
                        $dataList['orderStatus'] = "Pending";
                    } else if ($order_info['order_status'] == 1) {
                        $dataList['orderStatus'] = "Hold";
                    } else if ($order_info['order_status'] == 2) {
                        $dataList['orderStatus'] = "Packaging";
                    } else if ($order_info['order_status'] == 3) {
                        $dataList['orderCancled'] = 1;
                        $dataList['orderStatus'] = "Cancel";

                        if ($order_info->payment_status != 2) {
                            $dataList['refundNotice'] = 1;
                        }

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

    public function getOrderInfoListByOrderNumberNew(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderId = (is_null($request->orderId) || empty($request->orderId)) ? "" : $request->orderId;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $order = $this->OrderEn->get_by_id($orderId);
                $order_info = $this->Order->get_order_by_order_number_new($order->order);
              

                $direct_commision = 0;
                $team_commision = 0;
                $dataList = array();
                foreach ($order_info as $key => $value) {
                    $product_info = $this->Product->find_by_id($value['product_id']);
                    if($value['reseller_id'] == 0)
                    {
                        $dataList[$key]['resellPrice'] = 0;
                    }
                    else{
                        $resell_info = $this->ResellProduct->find_by_pid_and_sid($value['reseller_id'], $value['product_id']);
                        $dataList[$key]['resellPrice'] = $resell_info['price'];
                    }
                    

                    $dataList[$key]['productName'] = $product_info['product_name'];
                    $dataList[$key]['productPrice'] = $product_info['price'];
                   
                    $dataList[$key]['quantity'] = $value['quantity'];
                    $dataList[$key]['totalAmount'] = $value['total_amount'];
                    $dataList[$key]['OrderDate'] = date('Y-m-d', strtotime($value['created_at']));

                    $direct_commision += $product_info['direct_commision'];
                    $team_commision += $product_info['team_commision'];
                }

                $order_info = $this->OrderEn->getOrderInfoByOrderNumber($order->order);

                if ($order_info['payment_status'] == 0) {
                    $dataList['paymentStatus'] = "Pending";
                } else if ($order_info['payment_status'] == 1) {
                    $dataList['paymentStatus'] = "Paid";
                } else {
                    $dataList['paymentStatus'] = "Refunded";
                }

                if ($order_info['order_status'] == 0) {
                    $dataList['orderStatus'] = "Pending";
                } else if ($order_info['order_status'] == 1) {
                    $dataList['orderStatus'] = "Hold";
                } else if ($order_info['order_status'] == 2) {
                    $dataList['orderStatus'] = "Packaging";
                } else if ($order_info['order_status'] == 3) {
                    $dataList['orderStatus'] = "Cancle";
                } else if ($order_info['order_status'] == 4) {
                    $dataList['orderStatus'] = "In Courier";
                }else if ($order_info['order_status'] == 5) {
                    $dataList['orderStatus'] = "Delivered";
                } else if ($order_info['order_status'] == 6) {

                    if($order_info['return_status'] == 1){
                        $dataList['orderStatus'] = "Return and Received";
                    }
                    else if ($order_info['return_status'] == 2){
                         $dataList['orderStatus'] = "Return and Not Recived";
                     }
                   
                }  else {
                    $dataList['orderStatus'] = "Complted";
                }

                $dataList['orderCancled'] = 0;
                $dataList['cancleOrder'] = 0;

                if ($order_info['order_status'] < 4) {
                    $dataList['cancleOrder'] = 1;
                }

                if ($order_info['order_status'] == 3) {
                    $dataList['orderCancled'] = 1;
                }

                $dataList['directCommision'] = $direct_commision;
                $dataList['teamCommision'] = $team_commision;
                $dataList['totalAmount'] = $order_info['total_amount'];
               
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
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

                $resp = $this->OrderEn->update_pay_status_by_order($info);

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

                // $resp = $this->Order->update_order_status_by_order($info);
                $resp = $this->OrderEn->update_order_status_by_order($info);

                if ($resp) {

                    $profitShareInfo = array();

                    if ($orderStatus == 5) {
                        $order = $this->OrderEn->get_by_id($orderId);

                        $order_info_p = $this->Order->get_order_by_order_number_new($order->order);

                        foreach ($order_info_p as $key => $order_info) {
                            $product_info = $this->Product->find_by_id($order_info['product_id']);
                            $resell_info = $this->ResellProduct->get_by_seller_and_pid($order_info['reseller_id'], $order_info['product_id']);
                            $reseller_info = $this->Reseller->find_by_id($order_info['reseller_id']);

                            $profitShareInfo['resellerId'] = $order_info['reseller_id'];
                            $profitShareInfo['orderId'] = $order_info['id'];
                            $profitShareInfo['productId'] = $order_info['product_id'];
                            $profitShareInfo['productPrice'] = $product_info['price'];
                            $profitShareInfo['resellPrice'] = $resell_info['price'];
                            $profitShareInfo['quantity'] = $order_info['quantity'];
                            $profitShareInfo['totalAmount'] = $order_info['total_amount'];

                            $is_city_colombo = $this->isCityinsideColombo($order_info['city']);
                            $courir_charge = $this->getCourierCharge($is_city_colombo, $product_info['weight']);

                            // $profit = (($resell_info['price'] * $order_info['quantity']) - $product_info['price']) - $courir_charge;
                            $profit = ($order_info['total_amount'] - ($product_info['price'] * $order_info['quantity'])) ;

                            $direct_commision = ($product_info['price'] * ($product_info['direct_commision'] / 100) * $order_info['quantity']);

                            $profitShareInfo['deliveryCharge'] = $courir_charge;
                            $profitShareInfo['directCommision'] = $direct_commision;
                            $profitShareInfo['teamCommision'] = 0;
                            $profitShareInfo['profit'] = ($profit + $direct_commision);

                            $sellerProfitInfo = array();
                            $sellerProfitInfo['resellerId'] = $order_info['reseller_id'];
                            $sellerProfitInfo['profitTotal'] = ($reseller_info['profit_total'] + ($profit + $direct_commision));
                            $set_profit_total = $this->Reseller->set_profit_total($sellerProfitInfo);

                            if ($set_profit_total != null) {
                                $profitShareInfo['profitTotal'] = $set_profit_total['profit_total'];
                                $profitShareInfo['createTime'] = $this->AppHelper->get_date_and_time();
                                
                                $ref_list = $this->Reseller->get_ref_list_by_seller($reseller_info['ref_code']);
                                $profit_log = $this->ProfitShare->add_log($profitShareInfo);

                                $ref_profit_info = array();

                                foreach ($ref_list as $key => $value) {
                                    $ref_profit_info['resellerId'] = $value['id'];
                                    $ref_profit_info['orderId'] = 0;
                                    $ref_profit_info['productId'] = $order_info['product_id'];
                                    $ref_profit_info['productPrice'] = 0;
                                    $ref_profit_info['resellPrice'] = 0;
                                    $ref_profit_info['quantity'] = 0;
                                    $ref_profit_info['totalAmount'] = 0;
                                    $ref_profit_info['deliveryCharge'] = 0;
                                    $ref_profit_info['directCommision'] = 0;

                                    $team_commision = ($product_info['price'] * ($product_info['team_commision'] / 100));

                                    $ref_profit_info['teamCommision'] = $team_commision;
                                    $ref_profit_info['profit'] = 0;
            
                                    $ref_profit_info['profitTotal'] = ($value['profit_total'] + $team_commision);
                                    $ref_profit_info['createTime'] = $this->AppHelper->get_date_and_time();

                                    $profit_log = $this->ProfitShare->add_log($ref_profit_info);
                                    $sellerProfitInfo2['resellerId'] = $value['id'];
                                    $sellerProfitInfo2['profitTotal'] = ($value['profit_total'] + $team_commision);
                                    $set_profit_total2 = $this->Reseller->set_profit_total($sellerProfitInfo2);
                                }
                            }
                        }
                        
                    }

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
        $courierName = (is_null($request->courierName) || empty($request->courierName)) ? "" : $request->courierName;


        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Id is required.");
        } else if ($trackingNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Tracking Number is required.");
        } else if ($courierName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Courier Name is required.");
        } else {

            try {
                $info = array();
                $info['orderId'] = $orderId;
                $info['trackingNumber'] = $trackingNumber;
                $info['courierName'] = $courierName;

                $resp = $this->OrderEn->set_tracking_number_by_order($info);

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

    private function isCityinsideColombo($city) {
        $is_colombo_city = $this->City->validate_city($city, "Colombo");

        if ($is_colombo_city) {
            return true;
        } else {
            return false;
        }
    }

    private function getCourierCharge($is_colombo, $product_weight) {

        $default_charge = 300;
        $weight_in_kg = ($product_weight) / 1000;

        if ($weight_in_kg > 1) {
            $remaining = $weight_in_kg - 1;
            $round_remaining = ceil($remaining);
            
            if ($round_remaining > 0) {
                $default_charge += ($round_remaining * 50);
            }
        }

        if (!$is_colombo) {
            $default_charge += 50;
        }

        return $default_charge;
    }

    public function getOrderInfoListByOrderCusdetails(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderId = (is_null($request->Oid) || empty($request->Oid)) ? "" : $request->Oid;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Id is required.");
        }else{
            $OrderSerial = OrderEn::where('id', $orderId)->pluck('order');
            $Cusdetails = Order::where('order', $OrderSerial)->first(['name', 'contact_1', 'contact_2', 'address', 'order']);
            return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $Cusdetails);
        }

       

    }

    public function UpdateOrderInfoListByOrderCusdetails(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderNo = (is_null($request->order) || empty($request->order)) ? "" : $request->order;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Id is required.");
        }else{
           
            $Update = Order::where('order', $orderNo)->update(['contact_1' =>  $request->contact_1, 'contact_2'=> $request->contact_2, 'address'=> $request->address]);
            return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $orderNo);
        }
       
    }

    public function updateProductInfo(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderNo = (is_null($request->orderId) || empty($request->orderId)) ? "" : $request->orderId;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Id is required.");
        }else{
           
            $Update = OrderEn::where('id', $orderNo)->update(['return_status' =>  $request->returnstatus]);
            return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $orderNo);
        }
    }
}
