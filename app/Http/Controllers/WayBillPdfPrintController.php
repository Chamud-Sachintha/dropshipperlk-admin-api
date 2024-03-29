<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\Product;
use App\Models\Reseller;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;

class WayBillPdfPrintController extends Controller
{
    private $AppHelper;
    private $Order;
    private $Seller;
    private $Product;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Seller = new Reseller();
        $this->Product = new Product();
    }

    public function printWayBillPdf(Request $request) {

        $order_numbers = (is_null($request->orderNumbers) || empty($request->orderNumbers)) ? "" : $request->orderNumbers;

        if ($order_numbers == "") {
            return $this->AppHelper->responseMessageHandle("0", "Order Number sare required.");
        } else {    

            try {   

                $dataList = array();
                foreach ($order_numbers as $key => $value) {
                    $order_info = $this->Order->find_by_order_number($value);
                    $seller_info = $this->Seller->find_by_id($order_info['reseller_id']);
                    $product_info = $this->Product->find_by_id($order_info['product_id']);

                    $dataList[$key]['sellerName'] = $seller_info['full_name'];
                    $dataList[$key]['sellerMobile'] = $seller_info['phone_number'];
                    $dataList[$key]['customerName'] = $order_info['name'];

                    if ($order_info['payment_method'] == 1) {
                        $dataList[$key]['paymentMethod'] = "Bank Deposit";
                    } else if ($order_info['payment_method'] == 2) {
                        $dataList[$key]['paymentMethod'] = "Cash On Delivery";
                    } else {

                    }

                    $dataList[$key]['orderNumber'] = $value;
                    $dataList[$key]['customerAddress'] = $order_info['address'];
                    $dataList[$key]['customerMobile'] = $order_info['contact_1'];
                    $dataList[$key]['totalAmount'] = $order_info['total_amount'];
                    $dataList[$key]['productName'] = $product_info['product_name'];
                    $dataList[$key]['quantity'] = $order_info['quantity'];
                }

                $fileName = "fff";
                $pdf = PDF::loadView('pdf.way_bill', array('data' => $dataList))->setPaper('a4', 'portrait');

                return $pdf->stream($fileName.'.pdf');
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
