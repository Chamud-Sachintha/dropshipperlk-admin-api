<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\OrderCancle;
use App\Models\OrderEn;
use App\Models\Product;
use App\Models\ProfitShare;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Illuminate\Http\Request;

class BulkOrderUpdateController extends Controller
{
    private $AppHelper;
    private $Order;
    private $Product;
    private $Reseller;
    private $OrderCancleLog;
    private $ProfitShare;
    private $ResellProduct;
    private $OrderEn;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Product = new Product();
        $this->Reseller = new Reseller();
        $this->OrderCancleLog = new OrderCancle();
        $this->ProfitShare = new ProfitShare();
        $this->ResellProduct = new ResellProduct();
        $this->OrderEn = new OrderEn();
    }

    public function updateBulkOrder(Request $request) {
        $orderNumbersList = (is_null($request->orderNumbers) || empty($request->orderNumbers)) ? "" : $request->orderNumbers;

        if ($orderNumbersList == null) {
            return $this->AppHelper->responseMessageHandle(0, "Order Numbers are Empty.");
        } else {
            
        }
    }
}
