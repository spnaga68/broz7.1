<?php

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Model\api_model;
use App\Model\favorite_vendors;
use App\Model\outlets;
use App\Model\products;
use App\Model\stores;
use App\Model\vendors;
use DB;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Input;

DB::enableQueryLog();
use JWTAuth;
use Session;
use Tymon\JWTAuth\Exceptions\JWTException;
use URL;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Trigger extends Migration
{
    public function up()
    {
        DB::unprepared('CREATE TRIGGER user_default_role AFTER INSERT ON `orders` FOR EACH ROW
                BEGIN
                   INSERT INTO `trigger_log` (`comment`) VALUES (`sample`);
                END');
    }
    public function down()
    {
        DB::unprepared('DROP TRIGGER `user_default_role`');
    }
}
