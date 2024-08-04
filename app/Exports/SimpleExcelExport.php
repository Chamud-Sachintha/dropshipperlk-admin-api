<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\OrderEn;
use App\Models\OrderCancle;
use App\Models\BankDetails;
use App\Models\InCourierDetail;
use Exception;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

class SimpleExcelExport implements FromCollection
{
    private $selectedReportType;
    /**
     * @return \Illuminate\Support\Collection
     */

    private $Orders;
    private $Reseller;
    private $InCourier;

    public function __construct($selectedReportType)
    {
        $this->selectedReportType = $selectedReportType;
        $this->Orders = new Order();
        $this->Reseller = new Reseller();
        $this->InCourier = new InCourierDetail();
    }

    public function collection()
    {
        switch ($this->selectedReportType) {
            case 1:
                return $this->getOrdersReport();
            case 2:
                return $this->getResellersBankDetailsReport();
            default:
                return $this->getResellersPayoutReport();
        }
    }

    private function getOrdersReport()
    {
        $orders = Order::with(['product', 'orderEn'])->get();

        $dataArray = $orders->map(function ($order) {
            try {
                $courier_info = $this->InCourier->find_by_order_id($order->order);
                $product = $order->product;
                $orderEn = $order->orderEn;

                $prefix = substr($order->city, 0, 3);
                $is_colombo = $prefix === "Col";
                $courier_charge = $this->getCourierCharge($is_colombo, $product->weight);
                $fullAmount = $order->total_amount + $courier_charge;

                $statusMapping = [
                    0 => "Pending",
                    1 => "Hold",
                    2 => "Packaging",
                    3 => "Cancel",
                    4 => "In Courier",
                    5 => "Delivered",
                    6 => "Return Order",
                    7 => "Complete",
                ];

                $status = $statusMapping[$orderEn->order_status] ?? "Unknown";

                $refundStatus = $orderEn->order_status == 6 && $orderEn->return_status == 1 ? "Refunded" : "No Refund";

                $wayBillNo = null;
                if ($courier_info != null) {
                    $wayBillNo = $courier_info->way_bill;
                } else {
                    $wayBillNo = "-";
                }

                return [
                    'Order' => $order->order,
                    'Product Name' => $product->product_name,
                    'Tracking No' => $orderEn->tracking_number,
                    'Courier Name' => $orderEn->courier_name,
                    'Order Status' => $status,
                    'Name' => $order->name,
                    'Address' => $order->address,
                    'City' => $order->city,
                    'District' => $order->district,
                    'Contact 1' => $order->contact_1,
                    'Contact 2' => $order->contact_2,
                    'Quantity' => $order->quantity,
                    'Total Amount' => $fullAmount,
                    'Order Return Status' => $refundStatus,
                    'WayBill' => $wayBillNo
                ];
            } catch (\Exception $e) {
                // Log the error and skip this order
                // \Log::error('Error processing order ID: ' . $order->id . ' - ' . $e->getMessage());
                return null; // Skip this order
            }
        })->filter(); // Filter out any null entries

        $headers = [
            'Order ID', 'Product Name', 'Tracking No', 'Courier Name', 'Order Status', 'Name', 'Address',
            'City', 'District', 'Contact 1', 'Contact 2', 'Quantity', 'Total Amount', 'Order Return Status','WayBill'
        ];

        $dataArray->prepend($headers);

        return $dataArray;
    }

    private function getResellersBankDetailsReport()
    {
        $resellers = Reseller::with('bankDetails')->get();

        $dataArray = $resellers->map(function ($reseller) {
            $bankDetails = $reseller->bankDetails;

            return [
                'Reseller name' => $reseller->full_name,
                'Referral Code' => $reseller->code,
                'Bank Name' => $bankDetails->bank_name,
                'Account Number' => $bankDetails->account_number,
                'Branch' => $bankDetails->branch_code,
                'Reseller Bank Name' => $bankDetails->resellr_name,
            ];
        });

        $headers = [
            'Reseller name', 'Referral Code', 'Bank Name', 'Account Number', 'Branch', 'Reseller Bank Name',
        ];

        $dataArray->prepend($headers);

        return $dataArray;
    }

    private function getResellersPayoutReport()
    {
        $resellers = $this->Reseller->get();

        $dataArray = $resellers->map(function ($reseller) {
            $payout = $reseller->profit_total ? "LKR. " . number_format((float)$reseller->profit_total, 2, '.', '') : "LKR. 0.00";

            return [
                'Reseller name' => $reseller->full_name,
                'Referral Code' => $reseller->code,
                'Pending Payout' => $payout,
            ];
        });

        $headers = [
            'Reseller name', 'Referral Code', 'Pending Payout',
        ];

        $dataArray->prepend($headers);

        return $dataArray;
    }

    private function getCourierCharge($is_colombo, $product_weight)
    {
        $default_charge = 350;

        if (!is_numeric($product_weight)) {
            throw new InvalidArgumentException("Product weight must be a numeric value.");
        }

        $weight_in_kg = $product_weight / 1000;
        if ($weight_in_kg > 1) {
            $remaining = ceil($weight_in_kg - 1);
            $default_charge += $remaining * 50;
        }

        // if (!$is_colombo) {
        //     $default_charge += 50;
        // }

        return $default_charge;
    }
}
