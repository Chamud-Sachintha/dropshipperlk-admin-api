<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\PayoutLog;
use App\Models\ProfitShare;
use App\Models\Reseller;
use App\Models\BankDetails;
use App\Models\OrderEn;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PayoutLogController extends Controller
{
    private $AppHelper;
    private $Reseller;
    private $PayOutLog;
    private $Orders;
    private $ProfitShare;
    private $BankDetails;
    private $Product;
    private $OrderEn;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Reseller = new Reseller();
        $this->PayOutLog = new PayoutLog();
        $this->Orders = new Order();
        $this->ProfitShare = new ProfitShare();
        $this->BankDetails = new BankDetails();
        $this->OrderEn = new OrderEn();
        $this->Product = new Product();
    }

    public function getPayOutInfoBySeller(Request $request) {

        $sellerId = (is_null($request->sellerId) || empty($request->sellerId)) ? "" : $request->sellerId;

        if ($sellerId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Reseller ID is Required.");
        } else {

            try {
                $seller_info = $this->Reseller->find_by_id($sellerId);
                $total_pay_out_amount = $this->PayOutLog->get_total_payout_by_seller($sellerId);
                $today_pay_out = $this->PayOutLog->get_today_pay_amount($sellerId);
                $Bankdata =  $this->BankDetails->find_id($sellerId);

                $dataList = array();
                $dataList['totalPayOutAmount'] = $total_pay_out_amount;
                $dataList['totalPendingAmount'] = $seller_info['profit_total'];
                $dataList['todayPayOutAmount'] = $today_pay_out;
                $dataList['bank_name'] = $Bankdata->bank_name ?? '';
                $dataList['account_number'] = $Bankdata->account_number ?? '';
                $dataList['branch_code'] = $Bankdata->branch_code ?? '';
                $dataList['resellr_name'] = $Bankdata->resellr_name ?? '';
                
                $all_log = $this->PayOutLog->find_all_by_seller($sellerId);

                $dataList['list'] = [];

                if (!empty($all_log)) {
                    foreach ($all_log as $key => $value) {
                        $dataList['list'][$key] = [
                            'sellerName' => $seller_info['full_name'],
                            'beforeBalance' => $value['before_balance'],
                            'payOutAmount' => $value['pay_out_amount'],
                            'currentBalance' => $value['current_balance'],
                            'createDate' => $value['create_time']
                        ];
                    }
                }
                
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getSellerList(Request $request) {

        $resp = $this->Reseller->find_all();

        $dataList = array();
        foreach ($resp as $key => $value) {

            $order_info = $this->Orders->get_order_count_by_seller($value['id']);

            $dataList[$key]['sellerId'] = $value['id'];
            $dataList[$key]['resellerName'] = $value['full_name'];
            $dataList[$key]['resellerReferral'] = $value['code'];
            $dataList[$key]['totalOrders'] = $order_info;
            $dataList[$key]['pendingPayout'] = $value['profit_total'];
        }

        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
    }

    public function addPayOutLog(Request $request) {

        $amount = (is_null($request->amount) || empty($request->amount)) ? "" : $request->amount;
        $reseller_id = (is_null($request->sellerId) || empty($request->sellerId)) ? "" : $request->sellerId;

        if ($amount == "") {
            return $this->AppHelper->responseMessageHandle(0, "Amount is required.");
        } else if ($reseller_id == "") {
            return $this->AppHelper->responseMessageHandle(0, "Reseller ID is Reuires.");
        } else {

            try {
                $reseller_info = $this->Reseller->find_by_id($reseller_id);

                if ($amount > $reseller_info['profit_total']) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Amount");
                }

                $payOutLog = array();
                $payOutLog['resellerId'] = $reseller_id;
                $payOutLog['beforeBalance'] = $reseller_info['profit_total'];
                $payOutLog['payOutAmount'] = $amount;

                $current_balance = ($reseller_info['profit_total'] - $amount);

                $payOutLog['currentBalance'] = $current_balance;
                $payOutLog['createTime'] = $this->AppHelper->day_time();

                $payout_log = $this->PayOutLog->add_log($payOutLog);
                $set_profit_total = null;

                $profit_share_log = array();
                $profit_log = null;
                if ($payout_log) {
                    $info = array();
                    $info['resellerId'] = $reseller_id;
                    $info['profitTotal'] = $current_balance;

                    $set_profit_total = $this->Reseller->set_profit_total($info);

                    $profit_share_log['resellerId'] = $reseller_id;
                    $profit_share_log['orderId'] = 0;
                    $profit_share_log['productId'] = 0;
                    $profit_share_log['productPrice'] = 0;
                    $profit_share_log['resellPrice'] = 0;
                    $profit_share_log['quantity'] = 0;
                    $profit_share_log['totalAmount'] = 0;
                    $profit_share_log['deliveryCharge'] = 0;
                    $profit_share_log['directCommision'] = 0;
                    $profit_share_log['teamCommision'] = 0;
                    $profit_share_log['profit'] = 0;
                    $profit_share_log['logType'] = 2;

                    $profit_share_log['profitTotal'] = 0;
                    $profit_share_log['createTime'] = $this->AppHelper->get_date_and_time();

                    $profit_log = $this->ProfitShare->add_log($profit_share_log);
                }

                if ($payout_log && $set_profit_total && $profit_log) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getPayOutSummeryInfo(Request $request) {
        $resp = $this->Reseller->find_all();

        $totalPayOutAmount = 0; $totalPendingAmount = 0;
        foreach ($resp as $key => $value) {
            $total_pay_out_amount = $this->PayOutLog->get_total_payout_by_seller($value['id']);

            $totalPendingAmount += $value['profit_total'];
            $totalPayOutAmount += $total_pay_out_amount;
        }

        $dataInfo = array();
        $dataInfo['totalPauOutAmount'] = number_format($totalPayOutAmount, 2, '.', ',');
        $dataInfo['totalPendingAmount'] = number_format($totalPendingAmount, 2, '.', ',');

        return $this->AppHelper->responseEntityHandle(1, "Operation Successfully.", $dataInfo);
    }

    public function getProfitShareLogBySeller(Request $request) {

        $resellerId = (is_null($request->sellerId) || empty($request->sellerId)) ? "" : $request->sellerId;

        if ($resellerId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Reseller ID");
        } else {
            try {
                $seller_info = $this->Reseller->find_by_id($resellerId);
                $resp = $this->ProfitShare->get_log_by_seller($seller_info['id']);

                $dataList = array();
                foreach ($resp as $key => $value) {
                    $product_info = $this->Product->find_by_id($value['product_id']);
                    $order_info = null;
                    if($value['order_id'] != 0){
                        $order_info = $this->Orders->find_by_id($value['order_id']);
                        $dataList[$key]['orderNumber'] =  $order_info['order'];
                    }
                    else{
                        
                        $dataList[$key]['orderNumber'] =  "-";
                    }

                   

                    if ($value['product_id'] != 0) {
                      //  $dataList[$key]['productName'] = $product_info['product_name'];
                      $dataList[$key]['productName'] = $product_info['product_name'];
                    } else {
                        $dataList[$key]['productName'] = 0;
                    }

                    if ($value['type'] == 1) {
                        $dataList[$key]['logType'] = "Transfer In";
                    } else {
                        $dataList[$key]['logType'] = "Transfer Out";
                    }

                    if ($product_info != null) {
                        $dataList[$key]['productPrice'] = $product_info['price'];
                    } else {
                        $dataList[$key]['productPrice'] = "Not Found";
                    }

                    $dataList[$key]['deliveryCharge'] = 0;

                    if ($order_info != null) {
                        $orderEnInfo = $this->OrderEn->getOrderInfoByOrderNumber($order_info['order']);

                        if ($orderEnInfo['payment_method'] != 3) {
                            $dataList[$key]['deliveryCharge'] = 350;
                        }
                    }

                    $dataList[$key]['resellPrice'] = $value['resell_price'];
                    $dataList[$key]['quantity'] = $value['quantity'];
                    $dataList[$key]['totalAmount'] = $value['total_amount'];
                    $dataList[$key]['profit'] = $value['profit'];
                    $dataList[$key]['directCommision'] = $value['direct_commision'];
                    $dataList[$key]['teamCommision'] = $value['team_commision'];
                    $dataList[$key]['profitTotal'] = $value['profit_total'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, "Error Occure " . $e->getMessage());
            }
        }
    }
}
