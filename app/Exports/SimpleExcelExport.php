<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Product;
use App\Models\Reseller;
use Maatwebsite\Excel\Concerns\FromCollection;

class SimpleExcelExport implements FromCollection
{
    private $selectedReportType;
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($selectedReportType)
    {
        $this->selectedReportType = $selectedReportType;
    }
    public function collection()
    {
        if ($this->selectedReportType == 1) {
            $data = Order::get();
    
            $dataArray = $data->map(function ($order) {
                $proname = Product::where('id', '=', $order->product_id)->pluck('product_name');
                $proweight = Product::where('id', '=', $order->product_id)->pluck('weight');
                $prefix = substr($order->city, 0, 3);
    
                if ($prefix == "Col") {
                    $is_colombo = true;
                    $courir_charge = $this->getCourierCharge($is_colombo, $proweight->first());
                    $FullAmount = $order->total_amount + $courir_charge;
                } else {
                    $is_colombo = false;
                    $courir_charge = $this->getCourierCharge($is_colombo, $proweight->first());
                    $FullAmount = $order->total_amount + $courir_charge;
                }
    
                return [
                    'Order' => $order->order,
                    'Product Name' => $proname->first(),
                    'Name' => $order->name,
                    'Address' => $order->address,
                    'City' => $order->city,
                    'District' => $order->district,
                    'Contact 1' => $order->contact_1,
                    'Contact 2' => $order->contact_2,
                    'Quantity' => $order->quantity,
                    'Total Amount' => $FullAmount,
                ];
            });
    
            $headers = [
                'Order ID', 'Product Name', 'Name', 'Address',
                'City', 'District', 'Contact 1', 'Contact 2', 'Quantity', 'Total Amount',
            ];
    
            $dataArray->prepend($headers);

                return $dataArray;
            }
        elseif($this->selectedReportType  == 2){
            
        }
        else{
           
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
