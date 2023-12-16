<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private $AppHelper;
    private $AdminUser;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->AdminUser = new AdminUser();
    }

    public function authenticateAdminUser(Request $request) {

        $userName = (is_null($request->userName) || empty($request->userName)) ? "" : $request->userName;
        $password = (is_null($request->password) || empty($request->password)) ? "" : $request->password;

        if ($userName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Username is required.");
        } else if ($password == "") {
            return $this->AppHelper->responseMessageHandle(0, "Password is required.");
        } else {

            try {
                $user = $this->AdminUser->find_by_username($userName);

                if ($user && Hash::check($password, $user['password'])) {

                    $token = $this->AppHelper->generateAuthToken($user);

                    $tokenInfo = array();
                    $tokenInfo['token'] = $token;
                    $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                    $token_time = $this->AdminUser->update_login_token($user['id'], $tokenInfo);

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $user, $token);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Credentials");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
