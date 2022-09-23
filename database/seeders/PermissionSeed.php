<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page\Page;
use App\Models\RolePermission\RolePermission;
use App\Models\Role\Role;
use App\Models\ButtonPermission\ButtonPermission;

class PermissionSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function roleSeed()
    {
        $roles =[
            ['id'=>1, 'name'=> 'Admin', 'is_active' => 1 ]
        ];
        Role::truncate()->insert($roles);
    }

    public function pageSeed()
    {
        $pages =[
            ['id'=>1, 'name'=> 'Setup', 'link'=> null, 'parent_id'=>0, 'translate'=>null, 'badge'=>null, 'type'=>'group', 'is_active' => 1 ],
            ['id'=>2, 'name'=> 'Contract Type', 'link'=> '/setup/contract-type', 'parent_id'=>1, 'translate'=>'Bank', 'badge'=>null, 'type'=>'basic', 'is_active' => 1 ],
            ['id'=>3, 'name'=> 'Country', 'link'=> '/setup/country', 'parent_id'=>1, 'translate'=>'Bank', 'badge'=>null, 'type'=>'basic', 'is_active' => 1 ],
            ['id'=>4, 'name'=> 'Organization', 'link'=> '/setup/organization', 'parent_id'=>1, 'translate'=>'Bank', 'badge'=>null, 'type'=>'basic', 'is_active' => 1 ],
            ['id'=>5, 'name'=> 'Counterparties', 'link'=>'/counterparties', 'parent_id'=>0, 'translate'=>'', 'badge'=>null, 'type'=>'basic', 'is_active' => 1 ],
            ['id'=>6, 'name'=> 'Contracts', 'link'=>'/contracts', 'parent_id'=>0, 'translate'=>'', 'badge'=>null, 'type'=>'basic', 'is_active' => 1 ],

        ];
        Page::truncate()->insert($pages);
    }

    public function rolePermissionSeed()
    {
        $rolePermission =[
            ['role_id'=> 1, 'page_id'=> 1, 'is_checked'=>1, 'permission'=>'fullaccess' ],
            ['role_id'=> 1, 'page_id'=> 2, 'is_checked'=>1, 'permission'=>'fullaccess' ],
            ['role_id'=> 1, 'page_id'=> 3, 'is_checked'=>1, 'permission'=>'fullaccess' ],
            ['role_id'=> 1, 'page_id'=> 4, 'is_checked'=>1, 'permission'=>'fullaccess' ],
            ['role_id'=> 1, 'page_id'=> 5, 'is_checked'=>1, 'permission'=>'fullaccess' ],
            ['role_id'=> 1, 'page_id'=> 6, 'is_checked'=>1, 'permission'=>'fullaccess' ],
        ];

        RolePermission::truncate()->insert($rolePermission);
    }

    public function buttonPermissionSeed()
    {
        $buttonPermission =[
            ['role_id'=> 1, 'page_id'=> 1, 'status'=>1],
            ['role_id'=> 1, 'page_id'=> 1, 'status'=>2],
            ['role_id'=> 1, 'page_id'=> 1, 'status'=>3],
            ['role_id'=> 1, 'page_id'=> 1, 'status'=>4],
            ['role_id'=> 1, 'page_id'=> 1, 'status'=>5],
            ['role_id'=> 1, 'page_id'=> 1, 'status'=>6],

            ['role_id'=> 1, 'page_id'=> 2, 'status'=>1],
            ['role_id'=> 1, 'page_id'=> 2, 'status'=>2],
            ['role_id'=> 1, 'page_id'=> 2, 'status'=>3],
            ['role_id'=> 1, 'page_id'=> 2, 'status'=>4],
            ['role_id'=> 1, 'page_id'=> 2, 'status'=>5],
            ['role_id'=> 1, 'page_id'=> 2, 'status'=>6],

            ['role_id'=> 1, 'page_id'=> 3, 'status'=>1],
            ['role_id'=> 1, 'page_id'=> 3, 'status'=>2],
            ['role_id'=> 1, 'page_id'=> 3, 'status'=>3],
            ['role_id'=> 1, 'page_id'=> 3, 'status'=>4],
            ['role_id'=> 1, 'page_id'=> 3, 'status'=>5],
            ['role_id'=> 1, 'page_id'=> 3, 'status'=>6],

            ['role_id'=> 1, 'page_id'=> 4, 'status'=>1],
            ['role_id'=> 1, 'page_id'=> 4, 'status'=>2],
            ['role_id'=> 1, 'page_id'=> 4, 'status'=>3],
            ['role_id'=> 1, 'page_id'=> 4, 'status'=>4],
            ['role_id'=> 1, 'page_id'=> 4, 'status'=>5],
            ['role_id'=> 1, 'page_id'=> 4, 'status'=>6],

            ['role_id'=> 1, 'page_id'=> 5, 'status'=>1],
            ['role_id'=> 1, 'page_id'=> 5, 'status'=>2],
            ['role_id'=> 1, 'page_id'=> 5, 'status'=>3],
            ['role_id'=> 1, 'page_id'=> 5, 'status'=>4],
            ['role_id'=> 1, 'page_id'=> 5, 'status'=>5],
            ['role_id'=> 1, 'page_id'=> 5, 'status'=>6],

            ['role_id'=> 1, 'page_id'=> 6, 'status'=>1],
            ['role_id'=> 1, 'page_id'=> 6, 'status'=>2],
            ['role_id'=> 1, 'page_id'=> 6, 'status'=>3],
            ['role_id'=> 1, 'page_id'=> 6, 'status'=>4],
            ['role_id'=> 1, 'page_id'=> 6, 'status'=>5],
            ['role_id'=> 1, 'page_id'=> 6, 'status'=>6],
        ];

        ButtonPermission::truncate()->insert($buttonPermission);
    }

    public function run()
    {
        $this->roleSeed();
        $this->pageSeed();
        $this->rolePermissionSeed();
        $this->buttonPermissionSeed();
    }
}
