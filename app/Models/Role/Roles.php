<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;
use App\Manager\PermissionManager\PermissionManager;

class Role extends BaseModel
{
    use HasFactory;
    public function __construct()
    {
        parent::__construct($this);
    }

    public function getPermissionAttribute()
    {
        return ( new PermissionManager())->getPageMenuMinTree($this->id);
    }

    public function serializerFields()
    {
        return ['id', 'name', 'permission', 'is_active', 'created_by', 'updated_by'];
    }

    public static function postserializerFields()
    {
        return ['name', 'is_active', 'created_by', 'updated_by'];
    }

    /**
     * @param NULL
     * @return Array
     */
    public static function fieldsValidator()
    {
        return [
            'name' => 'required',
        ];
    }
    /**
     * @param NULL
     * @return Array
     */
    public function exportTableFields()
    {
        return['id', 'name'];
    }
}
