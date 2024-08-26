<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\Order;

class ProductController extends Controller
{
    private $AppHelper;
    private $Product;
    private $Category;
    private $Order;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Product = new Product();
        $this->Category = new Category();
        $this->Order = new Order();
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
        $weight = (is_null($request->weight) || empty($request->weight)) ? "" : $request->weight;
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

                $validate_pname = $this->Product->find_by_p_name($productName);

                if ($validate_pname) {
                    return $this->AppHelper->responseMessageHandle(0, "Already Added");
                }

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
                    $productInfo['weight'] = $weight;
                    $productInfo['supplierName'] = $supplierName;
                    $productInfo['stockCount'] = $stockCount;

                    if($imageList){
                        $imageListData = array();
                        foreach ($imageList as $key => $value) {
                            $uniqueId = uniqid();
                            $ext = $value->getClientOriginalExtension();
    
                            $value->move(public_path('/images'), $uniqueId . '.' . $ext);
                            $imageListData[$key] = $uniqueId . '.' . $ext;
                        }
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
                    $category_info = $this->Category->find_by_id($value['category']);

                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['productName'] = $value['product_name'];
                    $decodedImages = json_decode($value['images']);

                    if ($decodedImages && isset($decodedImages->image0) && !empty($decodedImages->image0)){
                        // Assign the first image URL to $dataList[$key]['image']
                        $dataList[$key]['image'] = json_decode($value['images'])->image0;
                    } else {
                        // Set $dataList[$key]['image'] to an empty string
                        $dataList[$key]['image'] = '';
                    }
                    
                    $dataList[$key]['categoryName'] = $category_info['category_name'];
                    $dataList[$key]['description'] = $value['description'];
                    $dataList[$key]['price'] = $value['price'];
                    $dataList[$key]['createTime'] = $value['create_time'];
                    if($value['status'] == "1")
                    {
                        $dataList[$key]['status'] = "InStock";
                    }
                    else
                    {
                        $dataList[$key]['status'] = "OutOfStock";
                    }
                   
                }
               
               return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
             
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getProductInfoById(Request $request) {

        $productId = (is_null($request->productId) || empty($request->productId)) ? "" : $request->productId;

        if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Product->find_by_id($productId);

                $dataList = array();
                $dataList['productName'] = $resp['product_name'];
                $dataList['description'] = $resp['description'];
                $dataList['price'] = $resp['price'];
                $dataList['stockCount'] = $resp['stock_count'];
                $dataList['status'] = $resp['status'];
                $category_info = $this->Category->find_by_id($resp['category']);
                $dataList['categoryName'] = $category_info['category_name'];
                $decodedImages = json_decode($resp['images']);
                
                
                if ($decodedImages && isset($decodedImages->image0) && !empty($decodedImages->image0)){
                    // Assign the first image URL to $dataList[$key]['image']
                   // DD(json_decode($resp['images']));
                    foreach ($decodedImages as $key => $image) {
                    $dataList['image'][$key] = $image;
                    }
                } else {
                    // Set $dataList[$key]['image'] to an empty string
                    $dataList['image'] = '';
                }
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateProductInfo(Request $request) {
        $productId = (is_null($request->productId) || empty($request->productId)) ? "" : $request->productId;
        $productName = (is_null($request->productName) || empty($request->productName)) ? "" : $request->productName;
        $price = (is_null($request->price) || empty($request->price)) ? "" : $request->price;
        $category = (is_null($request->category) || empty($request->category)) ? "" : $request->category;
        $teamCommision = (is_null($request->teamCommision) || empty($request->teamCommision)) ? "" : $request->teamCommision;
        $directCommsion = (is_null($request->directCommision) || empty($request->directCommision)) ? "" : $request->directCommision;
        $isStorePick = (is_null($request->isStorePick) || empty($request->isStorePick)) ? "" : $request->isStorePick;
        $waranty = (is_null($request->warranty) || empty($request->warranty)) ? "" : $request->warranty;
        $description = (is_null($request->description) || empty($request->description)) ? "" : $request->description;
        $weight = (is_null($request->weight) || empty($request->weight)) ? "" : $request->weight;
        $supplierName = (is_null($request->supplierName) || empty($request->supplierName)) ? "" : $request->supplierName;
        $status = (is_null($request->status) || empty($request->status)) ? "" : $request->status;

        $stockCount = (is_null($request->stockCount) || empty($request->stockCount)) ? "" : $request->stockCount;

        $imageList = $request->files;
        $resultArray = [];
        if ($productName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Product Name is required.");
        } else if ($price == "") {
            return $this->AppHelper->responseMessageHandle(0, "Price is required.");
        } else {

            try {
                $productImages = $this->Product->find_by_id($productId);
                $jsonData = $productImages['images'];

                if($imageList){
                    $imageListData = array();
                    foreach ($imageList as $key => $value) {
                        $uniqueId = uniqid();
                        $ext = $value->getClientOriginalExtension();

                        $value->move(public_path('/images'), $uniqueId . '.' . $ext);
                        $imageListData[$key] = $uniqueId . '.' . $ext;
                    }
                }
                
                if($jsonData && $imageList){
                    $imageData = json_decode($jsonData, true);
                   
                    foreach ($imageData as $key => $value) {
                        $resultArray[$key] = $value;
                    }
                    $index = count($imageData); 
                    foreach ($imageListData as $key => $value) {
                        $resultArray["image" . ($index++)] = $value;
                    }
                  //  DD($resultArray);

                    $encodeImage =  json_encode($resultArray);
                }
                else{
                    $encodeImage =  json_encode($imageListData);
                }


               
                $productInfo = array();
                    $productInfo['productId'] = $productId;
                    $productInfo['productName'] = $productName;
                    $productInfo['price'] = $price;
                    $productInfo['category'] = $category;
                    $productInfo['teamCommision'] = $teamCommision;
                    $productInfo['directCommision'] = $directCommsion;
                    // $productInfo['isStorePick'] = ($isStorePick ? 1 : 0);
                    $productInfo['waranty'] = $waranty;
                    $productInfo['description'] = $description;
                    $productInfo['weight'] = $weight;
                    $productInfo['supplierName'] = $supplierName;
                    $productInfo['status'] = $request->status;
                    $productInfo['images'] = $encodeImage;
                    $productInfo['stockCount'] = $stockCount;

                    $resp = $this->Product->update_by_id($productInfo);

                    if ($resp) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occureed");
                    }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function deleteProduct(Request $request) {

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


    public function getProductdeleteById(Request $request){

        $productId = (is_null($request->productId) || empty($request->productId)) ? "" : $request->productId;
    
        if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }
         else {
                $ordecheck = $this->Order->find_by_order_id($productId);
            try {

                if($ordecheck){
                    return $this->AppHelper->responseMessageHandle(0, "Product is already in Order. Can't Delete");
                }
                else{
                    $productImages = $this->Product->find_by_id($productId);

                    $jsonData = $productImages['images'];
                    $imageData = json_decode($jsonData, true);
                    $imageDirectory = public_path('\images');
    
                    foreach ($imageData as $key => $filename) {
                       
                        $imageFilePath = $imageDirectory . '\\' . $filename;
                       
                        if (file_exists($imageFilePath)) {
                            unlink($imageFilePath);
                            echo "Deleted: " . $imageFilePath . "\n";
                        } else {
                            echo "File not found: " . $imageFilePath . "\n";
                        }
                    }
    
                    $resp = $this->Product->delete_by_id($productId);
                  
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $resp);
                }
               
               
    
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getProductimagedeleteById(Request $request){
        $Imagename = (is_null($request->imageId) || empty($request->imageId)) ? "" : $request->imageId;
        $productId = (is_null($request->productId) || empty($request->productId)) ? "" : $request->productId;
    
        if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }
         else {
               
            try {

                
                    $productImages = $this->Product->find_by_id($productId);

                    $jsonData = $productImages['images'];
                    $imageData = json_decode($jsonData, true);
                   
                    $imageData = array_filter($imageData, function ($image) use ($Imagename) {
                        return $image !== $Imagename;
                    });
                  
                    $imageDirectory = public_path('\images');                       
                    $imageFilePath = $imageDirectory . '\\' . $Imagename;
                       
                        if (file_exists($imageFilePath)) {
                            unlink($imageFilePath);
                            echo "Deleted: " . $imageFilePath . "\n";
                        } else {
                            echo "File not found: " . $imageFilePath . "\n";
                        }
                        $productInfo['images'] = json_encode($imageData);
                        $productInfo['productId'] = $productId;
                       // DD($productInfo);
                        $resp = $this->Product->update_images_by_id($productInfo);
                  
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $resp);
               
               
    
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
    
}
