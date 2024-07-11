<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AppHelper {

    public function responseMessageHandle($code, $message) {
        $data['code'] = $code;
        $data['message'] = $message;

        return $data;
    }

    public function responseEntityHandle($code, $msg, $response, $token = null) {

        $data['code'] = $code;
        $data['msg'] = $msg;
        $data['data'] = [$response];
        
        if ($token != null) {
            $data['token'] = $token;
        }

        return $data;
    }

    public function generateAuthToken($user) {
        $authCode = "CS Software Engineering" . $user . $this->day_time();
        return Hash::make($authCode);
    }

    public function day_time() {
        return strtotime(date("Ymd"));
    }

    public function get_date_and_time() {
        return strtotime("now");
    }

    public function decodeImage($imageData, $dir) {

        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
        $imageFileName = 'image_' . time() . Str::random(5) . '.png';

        // Storage::kyc('kyc')->put($imageFileName, $image);
        file_put_contents(public_path() . '/' . $dir . '/' . $imageFileName, $image);

        return $imageFileName;
    }

    public function generateUUID() {
        // Generate a UUID
        return Str::uuid();
    }

    public function generateRandomWayBill() {
        return mt_rand(0, 100000);
    }

    public function generateRandomNumber($length)
    {
        return substr(str_pad(mt_rand(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT), 0, $length);
    }
}

?>