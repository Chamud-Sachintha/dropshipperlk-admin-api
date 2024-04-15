<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\OrderEn;
use App\Models\OrderCancle;
use App\Models\BankDetails;
use Maatwebsite\Excel\Concerns\FromCollection;

class SimpleExcelExport implements FromCollection
{
    private $selectedReportType;
    /**
    * @return \Illuminate\Support\Collection
    */

    private $Orders;

    public function __construct($selectedReportType)
    {
        $this->selectedReportType = $selectedReportType;
        $this->Orders = new Order();
        $this->Reseller = new Reseller();
    }
    public function collection()
    {
        if ($this->selectedReportType == 1) {
            $data = Order::get();
    
            $dataArray = $data->map(function ($order) {
                $proname = Product::where('id', '=', $order->product_id)->pluck('product_name');
                $proweight = Product::where('id', '=', $order->product_id)->pluck('weight');
                $prefix = substr($order->city, 0, 3);
                $orderStatus = OrderEn::where('order','=',$order->order)->pluck('order_status')->first();
                $orderTracknumber = OrderEn::where('order','=',$order->order)->pluck('tracking_number')->first();
                $orderCouriorName = OrderEn::where('order','=',$order->order)->pluck('courier_name')->first();
                $orderID = OrderEn::where('order','=',$order->order)->pluck('id')->first();
                $refundStatus = OrderEn::where('id','=',$orderID)->pluck('return_status')->first();
    
                if ($prefix == "Col") {
                    $is_colombo = true;
                    $courir_charge = $this->getCourierCharge($is_colombo, $proweight->first());
                    $FullAmount = $order->total_amount + $courir_charge;
                } else {
                    $is_colombo = false;
                    $courir_charge = $this->getCourierCharge($is_colombo, $proweight->first());
                    $FullAmount = $order->total_amount + $courir_charge;
                }

                if ($orderStatus == 0) {
                    $StatusO = "Pending";
                } else if ($orderStatus == 1) {
                    $StatusO = "Hold";
                } else if ($orderStatus == 2) {
                    $StatusO = "Packaging";
                } else if ($orderStatus == 3) {
                    $StatusO = "Cancel";
                } else if ($orderStatus == 4) {
                    $StatusO = "In Courier";
                } else if ($orderStatus == 5) {
                    $StatusO = "Delivered";
                }else if ($orderStatus == 7) {
                    $StatusO = "Complete ";
                }
                else {
                    $StatusO = "Return Order";
                }
                if ($orderStatus == 6) {
                if($refundStatus == 1)
                {
                    $RefStatus = "Refunded";
                }
                else{
                    $RefStatus ="No Refunded";
                }
            }else{
                $RefStatus = "-";
            }

                return [
                    'Order' => $order->order,
                    'Product Name' => $proname->first(),
                    'Tracking No' => $orderTracknumber,
                    'Courier Name' => $orderCouriorName,
                    'Order Status' =>  $StatusO,
                    'Name' => $order->name,
                    'Address' => $order->address,
                    'City' => $order->city,
                    'District' => $order->district,
                    'Contact 1' => $order->contact_1,
                    'Contact 2' => $order->contact_2,
                    'Quantity' => $order->quantity,
                    'Total Amount' => $FullAmount,
                    'Order Return Status' =>  $RefStatus,
                ];
            });
    
            $headers = [
                'Order ID', 'Product Name','Tracking No','Courier Name','Order Status', 'Name', 'Address',
                'City', 'District', 'Contact 1', 'Contact 2', 'Quantity', 'Total Amount','Order Return Status',
            ];
    
            $dataArray->prepend($headers);

                return $dataArray;
               
        }
            elseif($this->selectedReportType  == 2)
            {   
               $data = Reseller::get();
                

                $dataArray = $data->map(function ($Banks) {
                    
                    $BankName = BankDetails::where('reselller_id','=',$Banks->id)->pluck('bank_name')->first();
                    $BankAccount = BankDetails::where('reselller_id','=',$Banks->id)->pluck('account_number')->first();
                    $BankBranchName = BankDetails::where('reselller_id','=',$Banks->id)->pluck('branch_code')->first();
                    $BankResellerName = BankDetails::where('reselller_id','=',$Banks->id)->pluck('resellr_name')->first();
                   
                return [
                        
                        'Reseller name' => $Banks->full_name,
                        'Referral Code' => $Banks->code,
                        'Bank Name' => $BankName,
                        'Account Number' => $BankAccount,
                        'Branch' =>  $BankBranchName,
                        'Reseller Bank Name' => $BankResellerName,
                    
                    ];
                });
        
                $headers = [
                    'Reseller name', 'Referral Code','Bank Name','Account Number','Branch', 'Reseller Bank Name',
                ];
        
                $dataArray->prepend($headers);

                    return $dataArray;
            }
        else{
            $resp = $this->Reseller->find_all();

            

            $dataArray = $resp->map(function ($value) {
              
                if($value->profit_total == null || 0)
                {
                    $payout = "LKR. 0.00";
                }
                else
                {
                   $payout = "LKR. ".number_format((float)$value->profit_total, 2, '.', '');
                }
               
            return [
                    
                    'Reseller name' => $value->full_name,
                    'Referral Code' => $value->code,
                    'Pending Payout' =>  $payout,
                   
                
                ];
            });
    
            $headers = [
                'Reseller name', 'Referral Code','Pending Payout',
            ];
    
            $dataArray->prepend($headers);

                return $dataArray;
        }
        
    }

    private function getCourierCharge($is_colombo, $product_weight) {

        $default_charge = 300;
        //dd($product_weight);
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
}
