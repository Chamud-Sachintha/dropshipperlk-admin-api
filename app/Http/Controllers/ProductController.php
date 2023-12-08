<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $AppHelper;
    private $Product;
    private $Category;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Product = new Product();
        $this->Category = new Category();
    }

    public function addNewProduct(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $productName = (is_null($request->productName) || empty($request->productName)) ? "" : $request->productName;
        $price = (is_null($request->price) || empty($request->price)) ? "" : $request->price;
        $category = (is_null($request->cetegory) || empty($request->category)) ? "" : $request->category;
        $teamCommision = (is_null($request->teamCommision) || empty($request->teamCommision)) ? "" : $request->teamCommsion;
        $directCommsion = (is_null($request->directCommision) || empty($request->directCommision)) ? "" : $request->directCommision;
        $isStorePick = (is_null($request->isStorePick) || empty($request->isStorePick)) ? "" : $request->isStorePick;
        $waranty = (is_null($request->waranty) || empty($request->waranty)) ? "" : $request->waranty;
        $description = (is_null($request->description) || empty($request->description)) ? "" : $request->description;
        $supplierName = (is_null($request->supplierName) || empty($request->supplierName)) ? "" : $request->supplierName;
        $stockCount = (is_null($request->stockCount) || empty($request->stockCount)) ? "" : $request->stockCount;

        if ($request_token == "") {

        } else if ($flag == "") {

        } else if ($productName == "") {

        } else if ($price == "") {

        } else if ($category == "") {

        } else if ($teamCommision == "") {

        } else if ($directCommsion == "") {

        } else if ($isStorePick == "") {

        } else if ($waranty == "") {

        } else if ($description == "") {

        } else if ($supplierName == "") {

        } else if ($stockCount == "") {

        } else {

            try {

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
