<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\InCourierDetail;
use Illuminate\Http\Request;

class InCourierDetailController extends Controller
{
    private $InCourierDetail;
    private $AppHelper;

    private $APP_KEY = "599c2220-e9db-46f7-9477-cbe8ab69ac71";

    public function __construct()
    {
        $this->InCourierDetail = new InCourierDetail();
        $this->AppHelper = new AppHelper();
    }

    public function getPackageReadyOrderList(Request $request) {
        $pendingPackageList = $this->InCourierDetail->get_pending_list();

        $pendingListArray = [];
        foreach ($pendingPackageList as $eachPackage) {
            $pendingListArray['id'] = $eachPackage['id'];
            $pendingListArray['orderNumber'] = $eachPackage['order'];
            $pendingListArray['wayBillNo'] = $eachPackage['way_bill'];

            $response = json_decode($this->trackPackageQuery($eachPackage['way_bill']));

            if ($response->success == true) {
                $pendingListArray['packageCreateStatus'] = $response['data']['status'];
            } else {
                $pendingListArray['packageCreateStatus'] = "Pending";
            }

            $pendingListArray['createTime'] = $eachPackage['create_time'];
        }

        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $pendingListArray);
    }

    private function trackPackageQuery($wayBillNo) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://api.ceylonex.lk/api/v1/get-package?api_key=" . $this->APP_KEY . "&waybill=" . $wayBillNo);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-formurlencoded'));
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $server_output = curl_exec ($ch);

        curl_close($ch); 

        return $server_output;
    }
}
