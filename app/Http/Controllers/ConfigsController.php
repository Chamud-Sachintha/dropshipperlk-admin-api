<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Configs;
use Exception;
use Illuminate\Http\Request;

class ConfigsController extends Controller
{
    private $AppHelper;
    private $Configs;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Configs = new Configs();
    }

    public function addNewConfig(Request $request) {
        $configName = (is_null($request->configName) || empty($request->configName)) ? "" : $request->configName;
        $configValue = (is_null($request->configValue) || empty($request->configValue)) ? "" : $request->configValue;

        if ($configName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Config Name");
        } else if ($configValue == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Config Value");
        } else {
            try {
                $configInfo['configName'] = $configName;
                $configInfo['configValue'] = $configValue;
                $configInfo['createTime'] = $this->AppHelper->day_time();

                $config_info = $this->Configs->add_log($configInfo);

                if ($config_info) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Success.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, "Error Occcuired. " . $e->getMessage());
            }
        }
    }

    public function getConfigByName(Request $request) {
        $configName = (is_null($request->configName) || empty($request->configName)) ? "" : $request->configName;

        if ($configName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Config Name");
        } else {
            try {
                $config_info = $this->Configs->find_by_config($configName);

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $config_info);
            } catch (Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, "Error Occured.". $e->getMessage());
            }
        }
    }
}
