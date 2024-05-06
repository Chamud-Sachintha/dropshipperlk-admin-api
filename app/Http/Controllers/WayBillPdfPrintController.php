<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\OrderEn;
use App\Models\Product;
use App\Models\Reseller;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SimpleExcelExport;
use Carbon\Carbon;


class WayBillPdfPrintController extends Controller
{
    private $AppHelper;
    private $Order;
    private $Seller;
    private $Product;
    private $OrderEn;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Seller = new Reseller();
        $this->Product = new Product();
        $this->OrderEn = new OrderEn();
    }

   /* public function printWayBillPdf(Request $request) {

        $order_numbers = (is_null($request->orderNumbers) || empty($request->orderNumbers)) ? "" : $request->orderNumbers;

        if ($order_numbers == "") {
            return $this->AppHelper->responseMessageHandle("0", "Order Number sare required.");
        } else {    

            try {   

                $dataList = array();
                foreach ($order_numbers as $key => $value) {
                    $order_info = $this->Order->find_by_order_number($value);
                    $order_Totalptice = $this->OrderEn->getOrderInfoByOrderNumber($value);

                
                   if($order_info['reseller_id'] == 0)
                    {
                        $dataList[$key]['sellerName'] = 'Direct purchase';
                        $dataList[$key]['sellerMobile'] = '0718858925';
                    }
                    else
                    {
                        $seller_info = $this->Seller->find_by_id($order_info['reseller_id']);
                        $dataList[$key]['sellerName'] = $seller_info['full_name'];
                        $dataList[$key]['sellerMobile'] = $seller_info['phone_number'];
                    }
                   
                    $product_info = $this->Product->find_by_id($order_info['product_id']);

                  
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
                    $dataList[$key]['customerMobile2'] = $order_info['contact_2'];
                    $dataList[$key]['totalAmount'] = $order_Totalptice['total_amount'];
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
    }*/

  /*  public function printWayBillPdf(Request $request) {
        $order_numbers = (is_null($request->orderNumbers) || empty($request->orderNumbers)) ? "" : $request->orderNumbers;
    
        if ($order_numbers == "") {
            return $this->AppHelper->responseMessageHandle("0", "Order Numbers are required.");
        } else {    
            try {   
                $dataList = array();
    
                foreach ($order_numbers as $key => $value) {
                    $order_info = $this->Order->find_by_order_number($value);
                    $order_Totalptice = $this->OrderEn->getOrderInfoByOrderNumber($value);
    
                  
                    foreach ($order_info as $key2 => $value2) {
                       
                        if($value2['reseller_id'] == 0)
                    {
                        $dataList[$key2]['sellerName'] = 'Direct purchase';
                        $dataList[$key2]['sellerMobile'] = '0718858925';
                    }
                    else
                    {
                        $seller_info = $this->Seller->find_by_id($value2['reseller_id']);
                        $dataList[$key2]['sellerName'] = $seller_info['full_name'];
                        $dataList[$key2]['sellerMobile'] = $seller_info['phone_number'];
                    }
                   
                    $product_info = $this->Product->find_by_id($value2['product_id']);

                  
                    $dataList[$key2]['customerName'] = $value2['name'];

                    if ($value2['payment_method'] == 1) {
                        $dataList[$key2]['paymentMethod'] = "Bank Deposit";
                    } else if ($value2['payment_method'] == 2) {
                        $dataList[$key2]['paymentMethod'] = "Cash On Delivery";
                    } else {

                    }

                    $dataList[$key2]['orderNumber'] = $value;
                    $dataList[$key2]['customerAddress'] = $value2['address'];
                    $dataList[$key2]['customerMobile'] = $value2['contact_1'];
                    $dataList[$key2]['customerMobile2'] = $value2['contact_2'];
                    $dataList[$key2]['totalAmount'] = $order_Totalptice['total_amount'];
                    $dataList[$key2]['productName'] = $product_info['product_name'];
                    $dataList[$key2]['quantity'] = $value2['quantity'];
               
    
                        
                    }
                   
                }
              
                DD($dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }*/


    public function printWayBillPdf(Request $request) {
        $order_numbers = (is_null($request->orderNumbers) || empty($request->orderNumbers)) ? "" : $request->orderNumbers;
    
        if ($order_numbers == "") {
            return $this->AppHelper->responseMessageHandle("0", "Order Numbers are required.");
        } else {    
            try {   
                $dataList = array();
    
                foreach ($order_numbers as $key => $value) {
                    $order_info = $this->Order->find_by_order_number($value);
                    $order_TotalPrice = $this->OrderEn->getOrderInfoByOrderNumber($value);
    
                    foreach ($order_info as $value2) {
                       
                        $customerExists = false;
                        $customerKey = null;
    
                        foreach ($dataList as $key3 => $data) {
                            if ($data['customerName'] == $value2['name']) {
                                $customerExists = true;
                                $customerKey = $key3;
                                break;
                            }
                        }
    
                      
                        if ($customerExists) {
                            $dataList[$customerKey]['productName'][] = $this->Product->find_by_id($value2['product_id'])['product_name'];
                           
                        } else {
                           
                            $dataList[] = [
                                'sellerName' => ($value2['reseller_id'] == 0) ? 'Direct purchase' : $this->Seller->find_by_id($value2['reseller_id'])['full_name'],
                                'sellerMobile' => ($value2['reseller_id'] == 0) ? '0718858925' : $this->Seller->find_by_id($value2['reseller_id'])['phone_number'],
                                'customerName' => $value2['name'],
                                'paymentMethod' => ($value2['payment_method'] == 1) ? "Bank Deposit" : (($value2['payment_method'] == 2) ? "Cash On Delivery" : ""),
                                'orderNumber' => $value,
                                'customerAddress' => $value2['address'],
                                'customerMobile' => $value2['contact_1'],
                                'customerMobile2' => $value2['contact_2'],
                                'totalAmount' => $order_TotalPrice['total_amount'],
                                'productName' => [$this->Product->find_by_id($value2['product_id'])['product_name']],
                                'quantity' => $value2['quantity'],
                            ];
                        }
                    }
                }
                $fileName = "fff";
                $pdf = PDF::loadView('pdf.way_bill', array('data' => $dataList))->setPaper('a4', 'portrait');

                return $pdf->stream($fileName.'.pdf');
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
    
    
    


    public function DownloadExcel(Request $request)
    {
        // You can add validation or authorization logic here if needed

        $selectedReportType = $request->input('selectedReportType');
        $token = $request->input('token');
        $typerepo ='';

       
        if( $selectedReportType == '1')
        {
            
            $typerepo = "Order_Report";
        }
        elseif ( $selectedReportType == '2' )
        {
           
            $typerepo = "Bank_Details";
        }
        else{
            $typerepo = "Customer_Report";
        }
        $export = new SimpleExcelExport($selectedReportType);

        $filename = $typerepo . '_' . Carbon::now()->format('Y-m-d_H-i-s');
       
        return Excel::download($export, $filename . '.xlsx');
    }
}
