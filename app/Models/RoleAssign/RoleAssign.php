<?php

namespace App\Models\RoleAssign;

use App\Models\Base\BaseModel;
use App\Models\Role\Role;

class RoleAssign extends BaseModel
{
    protected $table="users";

    public function __construct()
    {
        parent::__construct($this);
    }

    public function serializerFields()
    {
        return ['id', 'name','role_id', 'role__name', 'created_by', 'updated_by'];
    }

    static public function postserializerFields()
    {
        return ['role_id', 'is_active', 'created_by', 'updated_by'];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    static public function fieldsValidator()
    {
        return [
            'role_id' => 'required',
        ];
    }

    public function exportTableFields() {
        return['id', 'name'];
    }
}