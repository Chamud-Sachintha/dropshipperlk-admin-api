<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\InCourierDetail;
use App\Models\Order;
use App\Models\OrderCancle;
use App\Models\OrderEn;
use App\Models\Product;
use App\Models\ProfitShare;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Illuminate\Http\Request;

class BulkOrderUpdateController extends Controller
{
    private $AppHelper;
    private $Order;
    private $Product;
    private $Reseller;
    private $OrderCancleLog;
    private $ProfitShare;
    private $ResellProduct;
    private $OrderEn;
    private $InCourierDetail;

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
        $this->InCourierDetail = new InCourierDetail();
    }

    public function updateBulkOrder(Request $request) {
        $orderNumbersList = (is_null($request->orderNumbers) || empty($request->orderNumbers)) ? "" : $request->orderNumbers;
        $orderStatus = (is_null($request->orderStatus) || empty($request->orderStatus)) ? "" : $request->orderStatus;

        if ($orderNumbersList == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Numbers are Empty.");
        } if ($orderStatus == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Status is required.");
        } else {
            foreach ($orderNumbersList as $eachOrder) {
                try {
                    $info = array();
                    $info['orderId'] = $eachOrder;
                    $info['orderStatus'] = $orderStatus;
    
                    // $resp = $this->Order->update_order_status_by_order($info);
                    $resp = $this->OrderEn->update_order_status_by_order_bulk($info);

                    if ($resp) {
                        $profitShareInfo = array();
    
                        if ($orderStatus == 5) {
                            $order = $this->OrderEn->get_by_id($eachOrder);
    
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

                        if ($orderStatus == 4) {
                            $inCourierDetails['order'] = $eachOrder;
                            $inCourierDetails['wayBillNo'] = $this->AppHelper->generateRandomNumber(8);
                            $inCourierDetails['packageCreateStatus'] = 0;
                            $inCourierDetails['createTime'] = $this->AppHelper->day_time();

                            $this->InCourierDetail->add_log($inCourierDetails);
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
    }

    private function ceylonxDeliveryOrderPlacement($orderInfo) {
        $ch = curl_init();

        $appKey = "599c2220-e9db-46f7-9477-cbe8ab69ac71";

        $data = http_build_query(array(
            'api_key' => $appKey,
            'waybill' => $orderInfo['wayBill'],
            'city' => $orderInfo['city'],
            'client_ref' => $orderInfo['clientRef'],
            'cod' => $orderInfo['cod'],
            'recipient' => "test",
            'address' => "test",
            'package_type' => $orderInfo['packageType'],
            'weight' => $orderInfo['weight'],
            'phone[0]' => $orderInfo['mobileNumber']
        ));

        curl_setopt($ch, CURLOPT_URL, "https://api.ceylonex.lk/api/v1/create-package");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        if ($server_output === false) {
            // Handle error
            echo 'Curl error: ' . curl_error($ch);
        } else {
            // Handle success
            echo $server_output;
        }

        curl_close($ch);
        return $server_output;
    }
}