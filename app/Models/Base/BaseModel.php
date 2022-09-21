<?php

namespace App\Models\Base;

use App\Engine\HttpStatus;
use App\Engine\DbFields\Fields;
use App\Mixins\ModelMixins\ExportableModelMixin;
use App\Mixins\ModelMixins\ProtectedQueryMixin;
use App\Mixins\ModelMixins\ProtectedRoleMixin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Exception;

class BaseModel extends Model
{
    use ProtectedQueryMixin, ProtectedRoleMixin, ExportableModelMixin;

    public function __construct(Model $model)
    {
        $this->model = $model;
        try {
            $this->current_staff = Auth::user()->staff_id;
            $this->auth_user = Auth::user()->id;
            $this->roleFieldValue = $this->current_staff;
        }catch (Exception $ex) 
        {

        }

    }

    public function getAll($filter = null, $value = null, $resourcePerPage = 50, $orderBy = 'DESC', $is_role = null, $data_permission = 0, $orderbyField = 'id', $special_query = null)
    {
        $resourcePerPage = $resourcePerPage == 0 ? 10 : $resourcePerPage;
        $querySet = $this->model::orderBy($this->tableName . '.' . $orderbyField, $orderBy)->where($this->tableName . '.deleted_at', null);

        if (method_exists($this, 'excludeIds') && $special_query == 'is_exclude') {
            $querySet = $querySet->whereNotIn($this->tableName .'.'. $this->excludeField, $this->excludeIds());
        }
        // without cache
        if ($filter && $value) {
            $querySet = $querySet->where($filter, $value);
        } elseif ($filter) {
            $querySet = $this->appliedMullipleFilter($querySet, $filter);
        }
        
        if ($special_query) {
            $query_keys = explode(':', $special_query);
            if ($query_keys[0] == 'unique') {
                if (!$filter) {
                    $querySet = $this->joinTable($querySet);
                }
                $key = $this->getTableNameFromRelationKey($query_keys[1]);
                $querySet = $querySet->groupBy($key);
            }
        }

        if (method_exists($this, 'selectFields')) {
            $querySet->select($this->selectQueryField());
        }

        return $querySet->paginate($resourcePerPage);
    }


    public function getResourceById($request, $id, $filter = null, $orderBy = null, $order_by_field = null)
    {
        $orderbyField =  $order_by_field ? $order_by_field : 'id';
        $orderBy = $orderBy ? $orderBy : 'DESC';
        if ($id == 'all') {
            $querySet = $this->model::where('deleted_at', null);
            if ($filter) {
                $querySet = $this->appliedMullipleFilter($querySet, $filter);
            }
            $querySet = $querySet->orderBy($orderbyField, $orderBy);
            $resource = $querySet->first();
        } elseif ($request->field) {
            $field = $request->field;
            $querySet = $this->model::where('deleted_at', null)->where($field, $id);
            if ($filter) {
                $querySet = $this->appliedMullipleFilter($querySet, $filter);
            }
            $querySet = $querySet->orderBy($orderbyField, $orderBy);
            $resource = $querySet->first();
        } else {
            $resource = $this->model::where('deleted_at', null)->find($id);
        }

        if (empty($resource)) {
            return response()->json(['errors' => 'Resource not found', 'status' => 404], 200);
        }

        // if ($request->version) {
        //     $version = Version::where('versionable_id', $resource->id)
        //         ->where('version', $request->version)
        //         ->where('versionable_type', $this->getVersionableClassName())->first();
        //     if (!$version) {
        //         return response()->json(['errors' => 'Resource not found', 'status' => 404], 200);
        //     } else {
        //         $versionableDate = json_decode($version->model_data, true);
        //         foreach ($versionableDate as $field => $value) {
        //             $resource->$field = $value;
        //         }
        //         return $resource;
        //     }
        // }

        return $resource;
    }

    public function deleteResource($request, $id)
    {
        $resource = $this->model::find($id);

        if (empty($resource)) {
            return response()->json(['message' => 'Resource not found.'], 404);
        }

        $resource->deleted_at = Carbon::now();
        $resource->deleted_by = Auth::user()->id;
        $resource->save();

        // Delete Log Entity
        $logEntity = json_encode($resource);
        $url = $request->url();
        $modelName = get_class($this->model);
        //related foreign key deleted
        try {
            if (method_exists($this->model, 'forignkeyDelete')) {
                $this->relationDelete = $this->forignkeyDelete();
            }
            foreach ($this->relationDelete as $relation) {
                $field =  $relation->field;
                $relation->model::where('id', $resource->$field)->update(['deleted_at' => Carbon::now()]);
            }
        } catch (Exception $e) {
        }

        return response()->json(['message' => 'Resource delete successfully.'], 200);
    }

    public function createResource($request)
    {
        $serializerConfig = $this->createSerializer();
        $direct_fields = isset($serializerConfig['direct_fields']) ? $serializerConfig['direct_fields'] : [];
        $query_result = array();

        foreach ($direct_fields as $key => $field) {
            $tableName = $field['model']::getTableName();
            $querySet = $field['model']::query();

            if (Schema::hasColumn($tableName, 'is_active')) {
                $querySet = $querySet->where('is_active', 1)->orWhere('is_active', '1');
            }
            if (Schema::hasColumn($tableName, 'deleted_at')) {
                $querySet = $querySet->where('deleted_at', null);
            }

            $query_result[$field['level']] = $querySet->select($field['fields'])->get();
        }
        return isset($serializerConfig['other_fields']) && !empty($serializerConfig['other_fields']) ?
            array_merge($query_result, $serializerConfig['other_fields']) : $query_result;
    }

    public function bulkCreate($request)
    {
        $EntityModel = $this->model;
        $bulks = $request->bulks;
        $tableName =  $EntityModel->getTable();
        $ids = [];
        $user_id = Auth::user()->id;
        if (!empty($bulks)) {
            DB::beginTransaction();
            foreach ($bulks as $resource) {
                $resource['created_by'] = $user_id;
                $resource['updated_by'] = $user_id;
                $ids[] = DB::table($tableName)->insertGetId($resource);
            }
            DB::commit();
        }
        if ($ids) {
            return response()->json(['data' => $EntityModel::whereIn('id', $ids)->get()], 200);
        } else {
            return response()->json(['data' => []], 200);
        }
    }

    public function storeResource($request)
    {
        $this->bootVersionableTrait();
        $EntityModel = $this->model;
        $fields = $EntityModel::postserializerFields();
        $requestFields = $request->all();

        // Field resolver
        if (method_exists($EntityModel, 'fieldMutation')) {
            $resolverFields = $this->fieldMutation();
            foreach ($resolverFields as $resolver) {
                if (isset($request[$resolver['field']])) {
                    $request[$resolver['field']] = $resolver['method']($request[$resolver['field']]);
                }
            }
        }

        // bulk create
        if ($request->bulks) {
            $validator = Validator::make($request->bulks[0], $EntityModel::fieldsValidator());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages(), HttpStatus::STATUS => HttpStatus::UNPROCESSABLE_ENTITY], HttpStatus::UNPROCESSABLE_ENTITY);
            }
            return $this->bulkCreate($request);
        }
        
        $validator = Validator::make($request->all(), $EntityModel::fieldsValidator());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages(), HttpStatus::STATUS => HttpStatus::UNPROCESSABLE_ENTITY], HttpStatus::UNPROCESSABLE_ENTITY);
        }

        $resource = new $EntityModel();
        $createCommonFields = Fields::createCommonFields($fields, $resource);
        $resource = $createCommonFields['resource'];
        $fields = $createCommonFields['fields'];

        foreach ($fields as $field) {
            if (isset($request->$field)) {
                $resource->$field = $request->$field;
            }
        }

        $resource->save();
        // one to many field data insert
        $hasone2many = false;
        $self_key = 'id';
        $relative = [];
        foreach ($requestFields as $key => $field) {
            if (is_object($field) || is_array($field) && !empty($field)) {
                $f_indx = $this->getOne2ManyFieldIndex($key);
                if ($f_indx > -1) {
                    $relative = $this->one2manyFields[$f_indx];
                    $relation_field = $relative['relation_key'];
                    $field[0][$relation_field] = $resource->id;
                    if (method_exists($relative['relative_model'], 'fieldsValidator')) {
                        $v_fields = $relative['relative_model']::fieldsValidator();
                        $cheker = $this->validatorChecker($field[0], $v_fields);
                        if ($cheker) {
                            return $cheker;
                        } else {
                            foreach ($field as $idx => $_f) {
                                $field[$idx] = $_f;

                                // Many to many model mutation field
                                if (method_exists($relative['relative_model'], 'fieldMutation')) {
                                    $m2mModelInstance = new $relative['relative_model'];
                                    $resolverFields = $m2mModelInstance->fieldMutation();
                                    foreach ($resolverFields as $resolver) {
                                        if (isset($field[$idx][$resolver['field']])) {
                                            $field[$idx][$resolver['field']] = $resolver['method']($field[$idx][$resolver['field']]);
                                        }
                                    }
                                }

                                $_key =  isset($relative['self_key']) ? $relative['self_key'] : "id";
                                $self_key = $_key;
                                $field[$idx][$relation_field] = $resource->$_key;
                                $field[$idx]['created_by'] = Auth::user()->id;
                                $field[$idx]['company_id'] = Auth::user()->id;
                            }
                            $relative['relative_model']::insert($field);
                            $hasone2many = true;
                        }
                    }
                }
            }
        }

        $resource = $EntityModel::find($resource->id);
        if ($hasone2many) {
            if (isset($relative['associate_with'])) {
                $resource[$relative['associate_with']] = $relative['relative_model']::where($relation_field, $resource->$self_key)->get();
            } else {
                $resource[$relation_field] = $relative['relative_model']::where($relation_field, $resource->$self_key)->get();
            }
        }
        $logEntity = json_encode($resource);
        // Store Log Entity
        $url = $request->url();
        $modelName = get_class($EntityModel);
        return $resource;
    }

    public function bulkUpateResource($request)
    {
        $EntityModel = $this->model;
        $bulks = $request->bulks;
        $tableName =  $EntityModel->getTable();
        $ids = [];

        // Delete previous added resource
        if ($request->deletes) {
            $deletedIds = $request->deletes;
            DB::table($tableName)->whereIn('id', $deletedIds)->delete();
        }

        if (empty($bulks)) {
            return response()->json(['message' => 'Resource not found', HttpStatus::STATUS => HttpStatus::NOT_FOUND], 200);
        }

        $deleted_staff_id = null;
        $user_id = Auth::user()->id;

        if (!empty($bulks)) {
            DB::beginTransaction();
            if (isset($bulks[0]['staff_id'])) {
                $deleted_staff_id = $bulks[0]['staff_id'];
            }
            foreach ($bulks as $resource) {
                if (isset($resource['id']) && $resource['id']) {
                    $resource['updated_by'] = $user_id;
                    DB::table($tableName)->where('id', $resource['id'])->update($resource);
                    $ids[] = $resource['id'];
                } else {
                    $resource['created_by'] = $user_id;
                    $ids[] = DB::table($tableName)->insertGetId($resource);
                }
            }
            DB::commit();
        }

        if ($ids) {
            $staff_id = $deleted_staff_id ? $deleted_staff_id : $this->current_staff;
            if ($this->has_staff_id) {
                DB::table($tableName)->where('staff_id', $staff_id)->whereNotIn('id', $ids)->delete();
            }
            return response()->json(['data' => $EntityModel::whereIn('id', $ids)->get()], 200);
        } else {
            return response()->json(['data' => []], 200);
        }
    }

    public function updateResource($request, $id)
    {
        //  $this->bootVersionableTrait();
        $EntityModel = $this->model;
        $fields = $EntityModel::postserializerFields();
        $resource = $EntityModel::find($id);
        $oldLogEntity = $resource;
        $requestFields = $request->all();

        if (empty($resource)) {
            return response()->json(['message' => 'Resource not found', HttpStatus::STATUS => HttpStatus::NOT_FOUND], 200);
        }


        // Old object version create if not created yet
        // $this->oldVersionableSave($resource);

        // Field resolver
        if (method_exists($EntityModel, 'fieldMutation')) {
            $resolverFields = $this->fieldMutation();
            foreach ($resolverFields as $resolver) {
                if (isset($request[$resolver['field']])) {
                    $request[$resolver['field']] = $resolver['method']($request[$resolver['field']]);
                }
            }
        }
        foreach ($fields as $field) {
            if (isset($request->$field)) {
                $resource->$field = $request->$field;
            } elseif (array_key_exists($field, $requestFields)) {
                $resource->$field = null;
            }
        }

        $resource->updated_by =  isset(Auth::user()->id)  ? Auth::user()->id : 1;
        $resource->save();

        // one to many field data insert
        $hasone2many = false;
        $self_key = 'id';
        $relative = [];


        foreach ($requestFields as $key => $field) {
            if (is_object($field) || is_array($field) && !empty($field)) {
                $f_indx = $this->getOne2ManyFieldIndex($key);
                if ($f_indx > -1) {
                    $relative = $this->one2manyFields[$f_indx];
                    $relation_field = $relative['relation_key'];
                    $field[0][$relation_field] = $resource->id;
                    if (method_exists($relative['relative_model'], 'fieldsValidator')) {
                        $v_fields = $relative['relative_model']::fieldsValidator();
                        $cheker = false;
                        if ($cheker) {
                            return $cheker;
                        } else {
                            $new_resource = [];
                            foreach ($field as $idx => $_f) {
            
                                // Many to many model mutation field
                                if (method_exists($relative['relative_model'], 'fieldMutation')) {
                                    $m2mModelInstance = new $relative['relative_model'];
                                    $resolverFields = $m2mModelInstance->fieldMutation();
                                    foreach ($resolverFields as $resolver) {
                                        if (isset($_f[$resolver['field']])) {
                                            $_f[$resolver['field']] = $resolver['method']($_f[$resolver['field']]);
                                        }
                                    }
                                }

                                $_key =  isset($relative['self_key']) ? $relative['self_key'] : "id";
                                $self_key = $_key;
                                $_f[$relation_field] =  $resource->$self_key;

                                if (isset($_f['id']) && $_f['id']) {
                                    $relative['relative_model']::where('id', $_f['id'])->update($_f);
                                } else {
                                    $_f['created_by'] = Auth::user()->id;
                                    $new_resource[] = $_f;
                                }
                            }
                            if (!empty($new_resource)) {
                                $relative['relative_model']::insert($new_resource);
                            }
                            $hasone2many = true;
                        }
                    }
                }
            }
        }

        // one to many field data delete
        if ($request->deletes) {
            $deletesList = $request->deletes;
            foreach ($deletesList as $dlist) {
                foreach ($dlist as $d_key => $ids) {
                    $f_indx = $this->getOne2ManyFieldIndex($d_key);
                    $relative = $this->one2manyFields[$f_indx];
                    $relative['relative_model']::whereIn('id', $ids)->delete();
                }
            }
        }

        $resource = $EntityModel::find($resource->id);
        if ($hasone2many) {
            if (isset($relative['associate_with'])) {
                $resource[$relative['associate_with']] = $relative['relative_model']::where($relation_field, $resource->$self_key)->get();
            } else {
                $resource[$relation_field] = $relative['relative_model']::where($relation_field, $resource->$self_key)->get();
            }
        }
        $newLogEntity = $resource;
        $logEntity = [
            'previous' => $oldLogEntity,
            'current' => $newLogEntity
        ];
        $logEntity = json_encode($logEntity);
        // update Log Entity
        $url = $request->url();
        $modelName = get_class($EntityModel);
        return $resource;
    }
}
