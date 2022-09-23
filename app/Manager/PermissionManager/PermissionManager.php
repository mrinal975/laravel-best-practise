<?php

namespace App\Manager\PermissionManager;

use App\Models\ButtonPermission\ButtonPermission;
use App\Models\Page\Page;
use App\Models\RolePermission\RolePermission;

class PermissionManager
{
    public function getMenuTree($arrayForPages, $parent = 0)
    {
        $menuTree = [];
        foreach ($arrayForPages[$parent] as $page) {
            $newMenu = new stdClass();
            $newMenu->id = $page['id'];
            $newMenu->title = $page['name'];
            $newMenu->translate = $page['translate'];
            $newMenu->type = $page['type'];
            $newMenu->icon = $page['icon'];
            $newMenu->url = $page['link'];
            $newMenu->badge = $page['badge'];

            // check if there are children for this item
            if (isset($arrayForPages[$page['id']])) {
                // and here we use this nested function recursively
                $newMenu->children = $this->getMenuTree(
                    $arrayForPages,
                    $page['id']
                );
            }

            $menuTree[] = $newMenu;
        }

        return $menuTree;
    }

    public function hasPageAccess($pageId, $roleId)
    {
        $data = RolePermission::where('page_id', $pageId)->where('role_id', $roleId)->first();
        return $data?1:0;
    }

    public function getButtonPermission($pageId, $roleId)
    {
        return ButtonPermission::where('page_id', $pageId)->where('role_id', $roleId)->select('id', 'status')->get();
    }

    public function getPages($roleId, $parent)
    {
        return Page::join('role_permissions', 'pages.id', '=', 'role_permissions.page_id')
                ->where('role_permissions.role_id', $roleId)
                ->where('pages.parent_id', $parent)
                ->where('role_permissions.is_checked', 1)
                ->select('pages.*', 'role_permissions.is_checked')
                ->get()
                ->toArray();
    }

    public function getPageMenuMinTree($roleId)
    {
        $menuTree = [];
        $parentPages = $this->getPages($roleId, 0);
        // return $parentPages;
        foreach ($parentPages as $key=>$parentPages) {
            $newMenu = new \stdClass();
            $newMenu->id = $parentPages['id'];
            $newMenu->title = $parentPages['name'];
            $subMenus = $this->getPages($roleId, $parentPages['id']);
            $childMenu = [];
            foreach ($subMenus as $subMenu) {
                $subMenuobj = new \stdClass();
                $subMenuobj->id = $subMenu['id'];
                $subMenuobj->title = $subMenu['name'];
                $childMenu[] = $subMenuobj;
            }
            $newMenu->children = $childMenu;
            
            $menuTree[] = $newMenu;
        }
        return $menuTree;
    }

    public function getPageButtonPermission($roleId, $link)
    {
        return Page::join('button_permissions', 'page_id', '=', 'pages.id')
        ->where('button_permissions.role_id', $roleId)
        ->where('pages.link', $link)
        ->select('button_permissions.status')
        ->get();
    }
}
