<?php


namespace App\Utility;


use Illuminate\Support\Facades\Redis;

class GenericManager {
    public function store($key, $data): void {
        $dynamic_menus = json_encode($data);
        Redis::set($key, $dynamic_menus);
    }

    public function get($key) {
        $data = Redis::get($key);
        return json_decode($data, true);
    }

    public function exists($key): bool {
        if(Redis::exists($key)) {
            return true;
        }else {
            return false;
        }
    }
    
    public function delete($key)
    {
        Redis::del($key);
    }
}
