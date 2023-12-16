<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\KYCInformation;
use App\Models\Reseller;
use Illuminate\Http\Request;

class KYCInformationController extends Controller
{
    private $AppHelper;
    private $KYCModel;
    private $Seller;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->KYCModel = new KYCInformation();
        $this->Seller = new Reseller();
    }

    public function getAllKYCInfoList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $status = (is_null($request->status) || empty($request->status)) ? "" : $request->status;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Toke is required.");
        } else {

            try {

                $kycList = $this->KYCModel->get_all($status);

                $dataList = array();
                foreach ($kycList as $key => $value) {

                    $sellerInfo = $this->Seller->find_by_id($value['client_id']);

                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['sellerId'] = $value['client_id'];
                    $dataList[$key]['sellerName'] = $sellerInfo['full_name'];
                    $dataList[$key]['mobileNumber'] = $sellerInfo['phone_number'];
                    $dataList[$key]['address'] = $sellerInfo['address'];
                    $dataList[$key]['frontImage'] = $value['front_image_nic'];
                    $dataList[$key]['backImage'] = $value['back_image_nic'];
                    $dataList[$key]['status'] = $value['status'];
                    $dataList[$key]['createTime'] = $value['create_time']; 
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateKYCInformations(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $sellerId = (is_null($request->sellerId) || empty($request->sellerId)) ? "" : $request->sellerId;
        $status = (is_null($request->status) || empty($request->status)) ? "" : $request->status;

        if ($request_token == "") { 
            return $this->AppHelper->responseMessageHandle(0, "Toke is required.");
        } else if ($sellerId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Seller Id Is Required.");
        } else if ($status == "") {
            return $this->AppHelper->responseMessageHandle(0, "Status is required.");
        } else {

            try {
                $sellerInfo = $this->Seller->find_by_id($sellerId);

                if ($sellerInfo) {
                    $updateKyc = $this->KYCModel->update_kyc_record($sellerId, $status);

                    if ($updateKyc) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
