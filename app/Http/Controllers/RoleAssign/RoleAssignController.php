<?php

namespace App\Http\Controllers\RoleAssign;

use App\Http\Controllers\Base\BaseController;
use App\Models\RoleAssign\RoleAssign;

class RoleAssignController extends BaseController
{
    function __construct(RoleAssign $roleAssign)
    {
        $this->entityInstance = $roleAssign;
        parent::__construct();
    }

}