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
use App\Models\InCourierDetail;
use Carbon\Carbon;


class WayBillPdfPrintController extends Controller
{
    private $AppHelper;
    private $Order;
    private $Seller;
    private $Product;
    private $OrderEn;
    private $InCourierInfo;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Seller = new Reseller();
        $this->Product = new Product();
        $this->OrderEn = new OrderEn();
        $this->InCourierInfo = new InCourierDetail();
    }

    public function printWayBillPdf(Request $request)
    {
        $order_numbers = (is_null($request->orderNumbers) || empty($request->orderNumbers)) ? "" : $request->orderNumbers;

        if ($order_numbers == "") {
            return $this->AppHelper->responseMessageHandle("0", "Order Numbers are required.");
        } else {
            try {
                $dataList = array();

                foreach ($order_numbers as $key => $value) {
                    $order_info = $this->Order->find_by_order_number($value);
                    $order_TotalPrice = $this->OrderEn->getOrderInfoByOrderNumber($value);

                    $courier_info = $this->InCourierInfo->find_by_order_id($value);

                    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                    $image = $generator->getBarcode($courier_info->way_bill, $generator::TYPE_CODE_128);

                    $base64EncodedData = base64_encode($image);

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
                            $dataList[$customerKey]['quantity'] += $value2['quantity'];
                        } else {
                            $dataList[] = [
                                'sellerName' => ($value2['reseller_id'] == 0) ? 'Direct purchase' : $this->Seller->find_by_id($value2['reseller_id'])['b_name'],
                                'sellerMobile' => ($value2['reseller_id'] == 0) ? '0718858925' : $this->Seller->find_by_id($value2['reseller_id'])['phone_number'],
                                'customerName' => $value2['name'],
                                'paymentMethod' => ($value2['payment_method'] == 1) ? "Bank Deposit" : (($value2['payment_method'] == 2) ? "Cash On Delivery" : ""),
                                'orderNumber' => $value,
                                'customerAddress' => $value2['address'],
                                'customerCity' => $value2['city'],
                                'customerMobile' => $value2['contact_1'],
                                'customerMobile2' => $value2['contact_2'],
                                'totalAmount' => $order_TotalPrice['total_amount'],
                                'productName' => [$this->Product->find_by_id($value2['product_id'])['product_name']],
                                'quantity' => $value2['quantity'],
                                'barcode' => $base64EncodedData,
                                'wayBillNumber' => $courier_info->way_bill
                            ];
                        }
                    }
                }

                $chunks = array_chunk($dataList, 3); // Split into chunks of 3

                $pdf = PDF::loadView('pdf.way_bill', array('chunks' => $chunks))
                    ->setPaper('a4', 'portrait');

                return $pdf->stream('waybill.pdf');
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function DownloadExcel(Request $request)
    {
        // You can add validation or authorization logic here if needed
        set_time_limit(300); // 300 seconds = 5 minutes

        $selectedReportType = $request->input('selectedReportType');
        $token = $request->input('token');
        $typerepo = '';


        if ($selectedReportType == '1') {

            $typerepo = "Order_Report";
        } elseif ($selectedReportType == '2') {

            $typerepo = "Bank_Details";
        } else {
            $typerepo = "Customer_Report";
        }
        $export = new SimpleExcelExport($selectedReportType);

        $filename = $typerepo . '_' . Carbon::now()->format('Y-m-d_H-i-s');

        return Excel::download($export, $filename . '.xlsx');
    }
}
