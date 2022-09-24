<?php

namespace App\Http\Controllers\RolePermission;

use App\Engine\HttpStatus;
use App\Http\Controllers\Base\BaseController;
use App\Models\ButtonPermission\ButtonPermission;
use App\Manager\PermissionManager\PermissionManager;
use App\Models\RolePermission\RolePermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Role\Role;
use App\Models\Page\Page;
use App\Models\User;
use stdClass;

class RolePermissionController extends BaseController
{
    public function __construct(Role $role)
    {
        $this->entityInstance = $role;
        parent::__construct();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulks' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }
        $data = Role::where('id', $request->role_id)->first();
        if (!$data) {
            $data = new Role();
        }
        $data->name = $request->name;
        $data->is_active = $request->is_active;
        $data->save();

        $role_id = $data->id;

        if ($request->bulks) {
            $groupPermission = [];
            $selectIds = [];
            foreach ($request->bulks as $page) {
                $hasGroup = RolePermission::where('page_id', $page['id'])->where('role_id', $role_id)->first();
                if (!$hasGroup) {
                    $groupPermission [] = [
                        'role_id' => $role_id,
                        'page_id' => $page['id'],
                        'is_checked' => isset($page['is_checked']) ? $page['is_checked'] : 1,
                        'permission' => isset($page['permission']) ? $page['permission']: 'fullcontrol',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ];
                }
                $selectIds[] = $page['id'];
                if (isset($page['button']) && gettype($page['button'])=='array') {
                    $ButtonSelectIds = [];
                    foreach ($page['button'] as $bPermission) {
                        $buttonPermission = ButtonPermission::where('id', $bPermission['id'])->first();
                        if (!$buttonPermission) {
                            $buttonPermission = new ButtonPermission();
                            $buttonPermission->page_id = $page['id'];
                            $buttonPermission->role_id = $role_id;
                        }
                        $buttonPermission->status = $bPermission['status'];
                        $buttonPermission->save();
                        $ButtonSelectIds[] = $bPermission['status'];
                    }
                    ButtonPermission::whereNotIn('status', $ButtonSelectIds)
                    ->where('page_id', $page['id'])->where('role_id', $role_id)->delete();
                }
            }
            RolePermission::insert($groupPermission);
            RolePermission::whereNotIn('page_id', $selectIds)->where('role_id', $role_id)->delete();
            return response()->json(['data' => 'saved successfully!'], 200);
        }
        return response()->json(['errors' => 'something errors!']);
    }

    public function getEmployeeRoleId($user_id)
    {
        return User::where('id', $user_id)->value('role_id');
    }

    public function getPages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }

        if ($request->user_id) {
            $user_id = $request->user_id;
            $role_id = $this->getEmployeeRoleId($user_id);
            $arrayForPages = $this->getPermittedPageList($role_id);
            $data = $this->getMenuTree($arrayForPages);

            return response()->json(['data' => $data, HttpStatus::STATUS => HttpStatus::OK], HttpStatus::OK);
        } else {
            return ['message' => 'No page is defined for this user'];
        }
    }

    public function getPermittedPageList($role_id){
        $arrayForPages = [];
        $workablePageIds = $this->hasPageIds($role_id);
        $allPage = Page::orderBy('order', 'asc')
                ->whereIn('id', $workablePageIds)
                ->get();

        foreach ($allPage as $page) {
            $arrayForPages[$page->parent_id][] = $page;
        }
        return $arrayForPages;
    }

    public function hasPageIds($roleId)
    {
        $pages = RolePermission::select('page_id')
            ->where('role_id', $roleId)
            ->where('is_checked', 1)
            ->orderBy('page_id')
            ->whereNull('deleted_at')
            ->get();

        $parentArray = [];

        foreach ($pages as $page) {
            $tempPageId = $page->page_id ?? 0;

            while ($tempPageId > 0) {
                $checkParent = Page::orderBy('order', 'asc')
                            ->where('id', $tempPageId)
                            ->first();

                if ($checkParent->id ?? null) {
                    array_push($parentArray, $checkParent->id);
                }

                $tempPageId = $checkParent->parent_id ?? 0;
            }
        }

        $pagesWithParent = array_unique($parentArray);
        sort($pagesWithParent);

        return $pagesWithParent;
    }

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

    public function showPages(Request $request)
    {
        $pageList = $this->getPermittedPageList($request->role_id);
        $data = $this->getMenuTree($pageList);
        return response()->json(['data' => $data, HttpStatus::STATUS => HttpStatus::OK], HttpStatus::OK);
    }

    public function checkPagePermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required'
            ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }
        $hasPermission = RolePermission::join('pages', 'pages.id', '=', 'role_permissions.page_id')
                    ->where('pages.link', $request->link)
                    ->where('role_permissions.role_id', Auth::user()->role_id)
                    ->exists();
        return response()->json(['data' => $hasPermission, HttpStatus::STATUS => HttpStatus::OK], HttpStatus::OK);
    }

    public function getButtonPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'link' => 'required'
            ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }
        $data = (new PermissionManager())->getPageButtonPermission($request->role_id, $request->link);
        return response()->json(['data' => $data, HttpStatus::STATUS => HttpStatus::OK], HttpStatus::OK);
    }
}
