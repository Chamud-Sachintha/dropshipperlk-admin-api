<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\SiteBanner;
use Illuminate\Http\Request;

class SiteBannerController extends Controller
{
    private $AppHelper;
    private $SiteBanner;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->SiteBanner = new SiteBanner();
    }

    public function addSiteBannerImage(Request $request) {

        $banner_img = (is_null($request->bannerImage) || empty($request->bannerImage)) ? "" : $request->bannerImage;

        if ($banner_img == "") {
            return $this->AppHelper->responseMessageHandle(0, "Banner Image is required.");
        } else {
            $existimage = $this->SiteBanner->getbannerhistry();
            try {
                $imgInfo = array();
                $imgInfo['bannerImage'] = $this->AppHelper->decodeImage($banner_img, 'banners');
                $imgInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $resp = $this->SiteBanner->add_log($imgInfo);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
