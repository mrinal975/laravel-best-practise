<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Base\BaseController;
use Illuminate\Http\Request;
use App\Models\Role\Role;

class RoleController extends BaseController
{
    function __construct(Role $role)
    {
        $this->entityInstance = $role;
        parent::__construct();
    }
}
