<?php

namespace Database\Seeders;

use App\Helpers\AppHelper;
use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{

    private $AdminUser;
    private $AppHelper;

    public function __construct()
    {
        $this->AdminUser = new AdminUser();
        $this->AppHelper = new AppHelper();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUserInfo = array();
        $adminUserInfo['firstName'] = "Chamud Sachintha";
        $adminUserInfo['lastName'] = "Kulathunga";
        $adminUserInfo['email'] = "chamudsachintha666@gmail.com";
        $adminUserInfo['password'] = 123;
        $adminUserInfo['createTime'] = $this->AppHelper->get_date_and_time();
        
        $this->AdminUser->add_log($adminUserInfo);
    }
}
