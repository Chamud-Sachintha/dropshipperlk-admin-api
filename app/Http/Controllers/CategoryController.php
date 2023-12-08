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
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        $categoryName = (is_null($request->categoryName) || empty($request->categoryName)) ? "" : $request->categoryName;

        if ($request_token == "") {

        } else if ($flag == "") {

        } else if ($categoryName == "") {

        } else {

            try {

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateCategoryInfo(Request $request) {
        
    }
}
