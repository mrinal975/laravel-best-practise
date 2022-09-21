<?php

namespace App\Mixins\ModelMixins;

use \ReflectionClass;
use Illuminate\Support\Facades\Schema;

trait ProtectedFieldMixin
{
    protected $tableName;
    protected $CacheTable = false;

    // Handle role permission on data
    protected $isRoleWise = false;
    protected $roleField = 'staff_id';
    protected $roleFieldValue = null;
    protected $selfExclude = false;

    // Auth user
    public $current_staff = null;
    protected $auth_user = null;

    // API query fields
    public $clientQueryFields = array();

    // doa
    protected $is_doa = false;
    protected $doa_field = 'approved_by';


    // Bulk update
    protected $has_staff_id = true;

    // exclude field
    protected $excludeField = 'id';


    public function __get($field)
    {
        if (method_exists($this, 'render_' . $field)) {
            $method = 'render_' . $field;
            return $this->$method();
        } else {
            return $this->getAttribute($field);
        }
    }

    public function getForeignKeys()
    {
        $reflector = new ReflectionClass($this);
        $relations = [];
        foreach ($reflector->getMethods() as $reflectionMethod) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType) {
                if (in_array(class_basename($returnType->getName()), ['HasOne', 'BelongsTo'])) {
                    $relations[] = $reflectionMethod->getName();
                }
            }
        }
        return $relations;
    }

    public function isFieldExist($model, $field)
    {
        $tableName = $model::getTableName();
        if (Schema::hasColumn($tableName, $field)) {
            return true;
        } else {
            return true;
        }
    }
}
