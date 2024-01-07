<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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
        $productName = (is_null($request->productName) || empty($request->productName)) ? "" : $request->productName;
        $price = (is_null($request->price) || empty($request->price)) ? "" : $request->price;
        $category = (is_null($request->category) || empty($request->category)) ? "" : $request->category;
        $teamCommision = (is_null($request->teamCommision) || empty($request->teamCommision)) ? "" : $request->teamCommision;
        $directCommsion = (is_null($request->directCommision) || empty($request->directCommision)) ? "" : $request->directCommision;
        $isStorePick = (is_null($request->isStorePick) || empty($request->isStorePick)) ? "" : $request->isStorePick;
        $waranty = (is_null($request->warranty) || empty($request->warranty)) ? "" : $request->warranty;
        $description = (is_null($request->description) || empty($request->description)) ? "" : $request->description;
        $supplierName = (is_null($request->supplierName) || empty($request->supplierName)) ? "" : $request->supplierName;
        $stockCount = (is_null($request->stockCount) || empty($request->stockCount)) ? "" : $request->stockCount;

        $imageList = $request->files;

        // dd($imageList);

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($productName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Product Name is required.");
        } else if ($price == "") {
            return $this->AppHelper->responseMessageHandle(0, "Price is required.");
        } else if ($category == "") {
            return $this->AppHelper->responseMessageHandle(0, "Category is required.");
        } else if ($teamCommision == "") {
            return $this->AppHelper->responseMessageHandle(0, "Team Commision is required.");
        } else if ($directCommsion == "") {
            return $this->AppHelper->responseMessageHandle(0, "Direct Commision is required.");
        } else if ($waranty == "") {
            return $this->AppHelper->responseMessageHandle(0, "waranty is required.");
        } else if ($description == "") {
            return $this->AppHelper->responseMessageHandle(0, "description is required.");
        } else if ($supplierName == "") {
            return $this->AppHelper->responseMessageHandle(0, "supplier Name is required.");
        } else if ($stockCount == "") {
            return $this->AppHelper->responseMessageHandle(0, "Stock Count is required.");
        } else {

            try {
                $isValidCategory = $this->validateCategory($category);

                if ($isValidCategory) {
                    $productInfo = array();
                    $productInfo['productName'] = $productName;
                    $productInfo['price'] = $price;
                    $productInfo['category'] = $category;
                    $productInfo['teamCommision'] = $teamCommision;
                    $productInfo['directCommision'] = $directCommsion;
                    $productInfo['isStorePick'] = ($isStorePick ? 1 : 0);
                    $productInfo['waranty'] = $waranty;
                    $productInfo['description'] = $description;
                    $productInfo['supplierName'] = $supplierName;
                    $productInfo['stockCount'] = $stockCount;

                    $imageListData = array();
                    foreach ($imageList as $key => $value) {
                        $uniqueId = uniqid();
                        $ext = $value->getClientOriginalExtension();

                        $value->move(public_path('/images'), $uniqueId . '.' . $ext);
                        $imageListData[$key] = $uniqueId . '.' . $ext;
                    }

                    $productInfo['images'] = json_encode($imageListData);
                    $productInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $product = $this->Product->add_log($productInfo);

                    if ($product) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Category");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getProductList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Product->find_all();

                $dataList = array();
                foreach ($resp as $key => $value) {
                    $dataList[$key]['productName'] = $value[''];
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function validateCategory($categoryId) {

        $isValidCategory = false;

        try {
            $resp = $this->Category->find_by_id($categoryId);

            if ($resp) {
                $isValidCategory = true;
            }
        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
        }

        return $isValidCategory;
    }
}
