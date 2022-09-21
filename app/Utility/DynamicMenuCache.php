<?php


namespace App\Utility;
use Illuminate\Support\Facades\Redis;


class DynamicMenuCache
{
    public function storeMenus($group_id, $menus): void {
        $identify = $group_id.'dynamic_menu';
        $dynamic_menus = json_encode($menus);
        Redis::set($identify, $dynamic_menus);
    }

    public function getMenus($group_id) {
        $identify = $group_id.'dynamic_menu';
        $menus = Redis::get($identify);
        return json_decode($menus, true);
    }

    public function exists($group_id): bool {
        $identify = $group_id.'dynamic_menu';
        if(Redis::exists($identify)) {
            return true;
        }else {
            return false;
        }
    }
    public function delete($group_id) {
        $identify = $group_id.'dynamic_menu';
        Redis::del($identify);
    }
}
