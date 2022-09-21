<?php


namespace App\Utility;
use Illuminate\Support\Facades\Redis;

Trait UserManager {
    public static function userStore($name, $password, $user) {
        $identify = $name.$password;
        $user_ = json_encode($user);
        Redis::set($identify, $user_);
        return $user;
    }
    public static function getUser($name, $password) {
        $identify = $name.$password;
        $user = Redis::get($identify);
        $data['data']  = json_decode($user, true);
        return $data;
    }
    public static function exists($name, $password) {
        $identify = $name.$password;
        if(Redis::exists($identify)) {
            return true;
        }else {
            return false;
        }
    }
}
