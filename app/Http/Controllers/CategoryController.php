<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $AppHelper;
    private $Category;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Category = new Category();
    }

    public function addNewCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        $categoryName = (is_null($request->categoryName) || empty($request->categoryName)) ? "" : $request->categoryName;
        $status = (is_null($request->status) || empty($request->status)) ? "" : $request->status;
        $description = (is_null($request->description) || empty($request->description)) ? "" : $request->description;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required");
        } else if ($categoryName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Category Name is required.");
        } else if ($status == "") {
            return $this->AppHelper->responseMessageHandle(0, "Status is required.");
        } else {

            try {
                $validateCategory = $this->Category->find_by_name($categoryName);

                if (!empty($validateCategory)) {
                    return $this->AppHelper->responseMessageHandle(0, "Already Exists.");
                } else {
                    $categoryInfo = array();
                    $categoryInfo['categoryName'] = $categoryName;
                    $categoryInfo['status'] = $status;
                    $categoryInfo['description'] = $description;
                    $categoryInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $newCategory = $this->Category->add_log($categoryInfo);

                    if ($newCategory) {
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

    public function getAllCategoryList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Category->find_all();

                $categoryList = array();
                foreach ($resp as $key => $value) {
                    $categoryList[$key]['id'] = $value['id'];
                    $categoryList[$key]['categoryName'] = $value['category_name'];
                    $categoryList[$key]['status'] = $value['status'];
                    $categoryList[$key]['description'] = $value['description'];
                    $categoryList[$key]['createTime'] = $value['create_time'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $categoryList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function FindCategoryInfo(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
             
                $resp = $this->Category->find_by_id($request->CategoryId);
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $resp);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateCategoryInfo(Request $request) {
        $CategorytId = (is_null($request->id) || empty($request->id)) ? "" : $request->id;
        $CategorName = (is_null($request->categoryName) || empty($request->categoryName)) ? "" : $request->categoryName;
        $description = (is_null($request->description) || empty($request->description)) ? "" : $request->description;
        $status = (is_null($request->status) || empty($request->status)) ? "" : $request->status;

        if ($CategorName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Category Name is required.");
        } else if ($status == "") {
            return $this->AppHelper->responseMessageHandle(0, "Status is required.");
       
        } else {

            try {
               

                $CategoryInfo = array();
                    $CategoryInfo['categoryid'] = $CategorytId;
                    $CategoryInfo['categoryName'] = $CategorName;
                    $CategoryInfo['description'] = $description;
                    $CategoryInfo['status'] = $status;
                  

                   $resp = $this->Category->update_by_id($CategoryInfo);

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
}
