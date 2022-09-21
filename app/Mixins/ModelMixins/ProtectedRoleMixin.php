<?php

namespace App\Mixins\ModelMixins;

use App\Enums\Permission\DataPermissionEnum;
use App\Models\Employee\EmployeeInfo;
use App\Models\Employee\LineManager;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;

trait ProtectedRoleMixin
{
    protected function getGroupDataPermission()
    {
        $user_id = Auth::user()->id;
        $user = User::select('groups.data_permission')->join('groups', 'groups.id', '=', 'users.group_id')->where('users.id', $user_id)->first();
        if ($user->data_permission) {
            return $user->data_permission;
        }
        return -1;
    }

    private function getRelatedEmployeeList($relatedValue)
    {
        $ids = EmployeeInfo::where('division_head', $relatedValue)
            ->orWhere('hrbp', $relatedValue)->pluck('staff_id');
        $_ids = LineManager::where('manager_id', $relatedValue)->distinct('staff_id')->pluck('staff_id');
        $ids = $ids->merge($_ids)->unique();
        return $_ids;
    }

    /**
     * @param querySet $querySet
     * @return querySet
     */
    protected function applyRoleFilter($querySet, $data_permission, $fieldValue = null)
    {
        $this->roleFieldValue = $this->roleFieldValue ? $this->roleFieldValue :
        ($fieldValue ? $fieldValue : Auth::user()->staff_id);
        
        if ($data_permission) {
            $permission = $data_permission;
        } else {
            $permission = $this->getGroupDataPermission();
        }
        if ($permission == DataPermissionEnum::All()->getValue()) {
            return $querySet;
        } elseif ($permission == DataPermissionEnum::Owner()->getValue()) {
            $querySet->where($this->tableName.'.'.$this->roleField, $this->roleFieldValue);
            return $querySet;
        } elseif ($permission == DataPermissionEnum::Related()->getValue()) {
            $relationsIds = $this->getRelatedEmployeeList($this->roleFieldValue);
            if (!$this->selfExclude) {
                $relationsIds[] = $this->roleFieldValue;
            }
            $querySet->whereIn($this->tableName.'.'.$this->roleField, $relationsIds);
            return $querySet;
        }
    }

    /**
     * @param QuerySet
     * @return QuerySet
     */
    protected function responsibilityData($querySet)
    {
        $current_staff = Auth::user()->staff_id;
        $whosIds =  Helper::getDoaData($current_staff);
        $querySet->whereIn($this->tableName.'.'.$this->doa_field, $whosIds);
        return $querySet;
    }
}
