<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\city_list;
use App\Models\InCourierDetail;
use App\Models\Order;
use App\Models\OrderCancle;
use App\Models\OrderEn;
use App\Models\Product;
use App\Models\ProfitShare;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class InCourierDetailController extends Controller
{
    private $InCourierDetail;
    private $OrderEn;
    private $OrderItems;
    private $Product;
    private $Reseller;
    private $OrderCancleLog;
    private $ProfitShare;
    private $ResellProduct;
    private $AppHelper;
    private $City;

    private $APP_KEY = "599c2220-e9db-46f7-9477-cbe8ab69ac71";

    public function __construct()
    {
        $this->InCourierDetail = new InCourierDetail();
        $this->OrderEn = new OrderEn();
        $this->OrderItems = new Order();
        $this->Product = new Product();
        $this->Reseller = new Reseller();
        $this->OrderCancleLog = new OrderCancle();
        $this->ProfitShare = new ProfitShare();
        $this->ResellProduct = new ResellProduct();
        $this->AppHelper = new AppHelper();
        $this->City = new city_list();
    }

    public function createCourierPackage(Request $request)
    {
        $orderNumber = (is_null($request->orderNumber) || empty($request->orderNumber)) ? "" : $request->orderNumber;
        $wayBillNo = (is_null($request->wayBillNumber) || empty($request->wayBillNumber)) ? "" : $request->wayBillNumber;

        if ($orderNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Number is required.");
        } else if ($wayBillNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "WayBill Number is required.");
        } else {
            $orderInfo = $this->OrderEn->get_by_id_bulk($orderNumber);

            if ($orderInfo == null || empty($orderInfo) || !$orderInfo) {
                return $this->AppHelper->responseMessageHandle(0, "Invalid OrderNumber");
            }

            $orderItemInfo = $this->OrderItems->get_order_by_order_number_new($orderNumber);

            $ceylonxInfo = array();
            $ceylonxInfo['wayBill'] = $wayBillNo;
            $ceylonxInfo['city'] = $orderItemInfo[0]->city;
            $ceylonxInfo['clientRef'] = $orderNumber;
            $ceylonxInfo['cod'] = $orderInfo->total_amount;
            $ceylonxInfo['recipient'] = $orderItemInfo[0]->name;
            $ceylonxInfo['address'] = $orderItemInfo[0]->address;
            $ceylonxInfo['packageType'] = 'cod';
            $ceylonxInfo['weight'] = "1";
            $ceylonxInfo['mobileNumber'] = $orderItemInfo[0]->contact_1;

            $response = json_decode($this->ceylonxDeliveryOrderPlacement($ceylonxInfo));

            if ($response->success == true) {
                $packageUpdateInfo['orderNumber'] = $orderNumber;
                $packageUpdateInfo['status'] = 1;

                $this->InCourierDetail->update_package_create_status($packageUpdateInfo);
                return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Operation Failed.");
            }
        }
    }

    public function updatePackageStatus(Request $request)
    {
        $orderNumber = (is_null($request->orderNumber) || empty($request->orderNumber)) ? "" : $request->orderNumber;
        $orderStatus = (is_null($request->orderStatus) || empty($request->orderStatus)) ? "" : $request->orderStatus;

        if ($orderNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Number is required.");
        } else if ($orderStatus == "") {
            return $this->AppHelper->responseMessageHandle(0, "OrderStatus is required.");
        } else {
            $info = array();
            $info['orderId'] = $orderNumber;
            $info['orderStatus'] = $orderStatus;

            // $resp = $this->Order->update_order_status_by_order($info);
            $resp = $this->OrderEn->update_order_status_by_order_bulk($info);

            if ($resp) {
                $profitShareInfo = array();

                if ($orderStatus == 5) {
                    $order = $this->OrderEn->get_by_id_bulk($orderNumber);
                    $order_info_p = $this->OrderItems->get_order_by_order_number_new($order->order);

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
                        $profit = ($order_info['total_amount'] - ($product_info['price'] * $order_info['quantity']));

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
        }
    }

    public function removeCourierPackageLog(Request $request)
    {
        $id = (is_null($request->id) || empty($request->id)) ? "" : $request->id;

        if ($id == "") {
            return $this->AppHelper->responseMessageHandle(0, "ID is required.");
        } else {
            $delete_log = $this->InCourierDetail->delete_by_id($id);

            if ($delete_log) {
                return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Operation Failed.");
            }
        }
    }

    public function getPackageReadyOrderList(Request $request) {
        $pendingListArray = DB::table('in_courier_details')
            ->join('order_ens', 'in_courier_details.order', '=', 'order_ens.order')
            ->join('resellers', 'order_ens.reseller_id', '=', 'resellers.id')
            ->select('in_courier_details.*', 'order_ens.order_status', 'resellers.b_name', 'resellers.ref_code')
            ->where('order_ens.order_status', '<=', 4)
            ->get()
            ->map(function ($eachPackage) {
                $response = json_decode($this->trackPackageQuery($eachPackage->way_bill));
                dd($response);
                return [
                    'id' => $eachPackage->id,
                    'orderNumber' => $eachPackage->order,
                    'wayBillNo' => $eachPackage->way_bill,
                    'packageStatus' => $response->success ? $response->data->status : $response->message,
                    'packageCreateStatus' => $eachPackage->package_create_status == 0 ? "Pending" : "Created",
                    'orderStatus' => $this->mapOrderStatus($eachPackage->order_status),
                    'resellerName' => $eachPackage->b_name,
                    'refCode' => $eachPackage->ref_code,
                    'createTime' => $eachPackage->create_time,
                ];
            });
    
        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $pendingListArray);
    }
    
    private function mapOrderStatus($status) {
        $orderStatusMap = [
            0 => 'Pending',
            1 => 'Hold',
            2 => 'Packaging',
            3 => 'Cancel',
            4 => 'In Courier',
            5 => 'Delivered',
            7 => 'Complete',
            8 => 'Settled',
            9 => 'Return Received',
            10 => 'Ready to Change',
            11 => 'Rescheduled',
            6 => 'Return Order'
        ];
        return $orderStatusMap[$status] ?? 'Unknown Status';
    }
    


    private function ceylonxDeliveryOrderPlacement($orderInfo)
    {
        $ch = curl_init();

        $data = http_build_query(array(
            'api_key' => $this->APP_KEY,
            'waybill' => $orderInfo['wayBill'],
            'city' => $orderInfo['city'],
            'client_ref' => $orderInfo['clientRef'],
            'cod' => $orderInfo['cod'],
            'recipient' => $orderInfo['recipient'],
            'address' => $orderInfo['address'],
            'package_type' => $orderInfo['packageType'],
            'weight' => $orderInfo['weight'],
            'phone[0]' => $orderInfo['mobileNumber']
        ));

        curl_setopt($ch, CURLOPT_URL, "https://api.ceylonex.lk/api/v1/create-package");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $server_output = curl_exec($ch);

        curl_close($ch);
        return $server_output;
    }

    private function trackPackageQuery($wayBillNo)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.ceylonex.lk/api/v1/get-package?api_key=" . $this->APP_KEY . "&waybill=" . $wayBillNo);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-formurlencoded'));
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return $server_output;
    }

    private function isCityinsideColombo($city)
    {
        $is_colombo_city = $this->City->validate_city($city, "Colombo");

        if ($is_colombo_city) {
            return true;
        } else {
            return false;
        }
    }

    private function getCourierCharge($is_colombo, $product_weight)
    {

        $default_charge = 350;
        $weight_in_kg = ($product_weight) / 1000;

        if ($weight_in_kg > 1) {
            $remaining = $weight_in_kg - 1;
            $round_remaining = ceil($remaining);

            if ($round_remaining > 0) {
                $default_charge += ($round_remaining * 50);
            }
        }

        // if (!$is_colombo) {
        //     $default_charge += 50;
        // }

        return $default_charge;
    }
}
