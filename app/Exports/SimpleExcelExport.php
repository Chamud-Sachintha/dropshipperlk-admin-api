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
        if($this->selectedReportType  == 1)
            {
                $data = Order::get();

                $dataArray = $data->map(function ($order) {
                    $proname = Product::where('id', '=', $order->product_id)->pluck('product_name');
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
                        'Total Amount' => $order->total_amount,
                    
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
            $data = Order::get();

                $dataArray = $data->map(function ($order) {
                    $proname = Product::where('id', '=', $order->product_id)->pluck('product_name');
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
                        'Total Amount' => $order->total_amount,
                    
                    ];
                });

                $headers = [
                    'Order ID', 'Product Name', 'Name', 'Address',
                    'City', 'District', 'Contact 1', 'Contact 2', 'Quantity', 'Total Amount',
                    
                ];

                $dataArray->prepend($headers);

                return $dataArray;
        }
        else{
            $data = Order::get();

            $dataArray = $data->map(function ($order) {
                $proname = Product::where('id', '=', $order->product_id)->pluck('product_name');
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
                    'Total Amount' => $order->total_amount,
                
                ];
            });

            $headers = [
                'Order ID', 'Product Name', 'Name', 'Address',
                'City', 'District', 'Contact 1', 'Contact 2', 'Quantity', 'Total Amount',
                
            ];

            $dataArray->prepend($headers);

            return $dataArray;
        }
        
    }
}
