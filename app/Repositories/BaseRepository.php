<?php

namespace App\Repositories;

use App\Http\Resources\Base\BaseCollection;
use App\Http\Resources\Base\BaseResource;
use Illuminate\Database\Eloquent\Model;
use App\Interface\BaseInterface;
use Illuminate\Http\Request;
use App\Engine\HttpStatus;
use Exception;
class BaseRepository implements BaseInterface
{
    /**
     * Create a new repository instance.
     *
     * @param  Model  $entityInstance
     * @return void
     */
    private $per_page = 20;
    public function __construct(Model $entityInstance)
    {
        $this->entityInstance = $entityInstance;
        
    }

        /**
     * Display a listing of the resource.
     *
     * @return BaseCollection
     */
    public function index(Request $request)
    {
        try {
            $limit = $this->per_page;
            $order_by = 'DESC';
            $is_role = null;
            $data_permission = 0;
            $orderyByField = 'id';
            $special_query = $request->special_query ? $request->special_query : null;

            if (isset($request->per_page)) {
                $limit = (int) $request->per_page;
            }

            if (isset($request->order_by_field) && $request->order_by_field) {
                $orderyByField = $request->order_by_field;
            }

            if (isset($request->order_by)) {
                $order_by = $request->order_by;
            }
            if (method_exists($this->entityInstance, 'serializerFields')) {
                $serializerFields = $this->entityInstance->serializerFields();
            }
            if (isset($request->queryFields) && !empty($request->queryFields)) {
                $serializerFields = explode(',', $request->queryFields);
            }
            if ($request->includeFields && !empty($request->includeFields)) {
                $includeFields = explode(',', $request->includeFields);
                $serializerFields = array_merge($serializerFields, $includeFields);
            }
            if ($request->fixincludeFields && !empty($request->fixincludeFields)) {
                $serializerFields =  explode(',', $request->fixincludeFields);
            }
            if ($request->excludeFields && !empty($request->excludeFields)) {
                $serializerFields;
                $excludeFields = explode(',', $request->excludeFields);
                $serializerFields = array_diff($serializerFields, $excludeFields);
            }
            if (method_exists($this->entityInstance, 'serializerFields')) {
                BaseCollection::serializerFieldsSet($serializerFields);
            }

            // Role filter
            if (isset($request->is_role)) {
                $is_role = $request->is_role;
            }
            if (isset($request->data_permission)) {
                $data_permission = $request->data_permission;
            }

            if (isset($request->filter) && isset($request->value)) {
                $data = $this->entityInstance->getAll($request->filter, $request->value, $limit, $order_by, $is_role, $data_permission, $orderyByField, $special_query);
                if (method_exists($this->entityInstance, 'additional')) {
                    $newBaseCollection =  (new BaseCollection($data))->additional(['additional' => $this->entityInstance->additional($request)]);
                } else {
                    $newBaseCollection =  new BaseCollection($data);
                }
            } elseif (isset($request->filters)) {
                $data = $this->entityInstance->getAll($request->filters, $request->value, $limit, $order_by, $is_role, $data_permission, $orderyByField, $special_query);
                if (method_exists($this->entityInstance, 'additional')) {
                    $newBaseCollection =  (new BaseCollection($data))->additional(['additional' => $this->entityInstance->additional($request)]);
                } else {
                    $newBaseCollection =  new BaseCollection($data);
                }
            } else {
                $data = $this->entityInstance->getAll(null, null, $limit, $order_by, $is_role, $data_permission, $orderyByField, $special_query);

                if (method_exists($this->entityInstance, 'additional')) {
                    $newBaseCollection =  (new BaseCollection($data))->additional(['additional' => $this->entityInstance->additional($request)]);
                } else {
                    $newBaseCollection =  new BaseCollection($data);
                }
            }
            $newBaseCollection->getEntityModelInstance($this->entityInstance);
            return $newBaseCollection;
        } catch (Exception $ex) {
            return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], 200);
        }
    }

    /**
    * create a newly resource in storage.
    *
    * @param Request $request
    * @return BaseResource
    */
    public function create(Request $request)
    {
        $response = $this->entityInstance->createResource($request);
        return response()->json(['data' => $response, HttpStatus::STATUS => HttpStatus::OK], 200);
        try {
            $response = $this->entityInstance->createResource($request);
            return response()->json(['data' => $response, HttpStatus::STATUS => HttpStatus::OK], 200);
        } catch (Exception $ex) {
            return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], 200);
        }
    }

    /**
    * create a newly resource in storage.
    *
    * @param Request $request
    * @return BaseResource
    */
    public function edit(Request $request, $id)
    {
        $with = $this->entityInstance->getOne2ManyFieldWithKey();
        try {
            if ($request->field) {
                $field = $request->field;
                $result = $this->entityInstance::where($field, $id)->first();
            } else {
                $result = $this->entityInstance::find($id);
            }
            foreach ($with as $m2mField) {
                if (isset($result->$m2mField) && $result->$m2mField) {
                    $result->$m2mField;
                }
            }
            if ($result) {
                if (method_exists($this->entityInstance, 'createResource')) {
                    $options = $this->entityInstance->createResource($request);
                    $result['options'] = $options;
                }
                return response()->json(['data' => $result, HttpStatus::STATUS => HttpStatus::OK], 200);
            } else {
                return response()->json(['data' => 'Resource not found', HttpStatus::STATUS => HttpStatus::NOT_FOUND], 200);
            }
        } catch (Exception $ex) {
            return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return BaseResource
     */
    public function store(Request $request)
    {
        $result = $this->entityInstance->storeResource($request);
        return (is_object(json_decode($result))) === false ?  $result :  new BaseResource($result);
        try {
            $result = $this->entityInstance->storeResource($request);
            return (is_object(json_decode($result))) === false ?  $result :  new BaseResource($result);
        } catch (Exception $ex) {
            return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return BaseResource|JsonResponse
     */
    public function show(Request $request, $id)
    {
        $order_by = $request->order_by ?  $request->order_by : 'DESC';
        $order_by_field = $request->order_by_field ?  $request->order_by_field : null;
        try {
            // inital details view list generate
            if (method_exists($this->entityInstance, 'detailsViewSerializer')) {
                $serializerFields = $this->entityInstance->detailsViewSerializer();
            } else {
                $serializerFields = $this->entityInstance->serializerFields();
            }

            if (isset($request->queryFields) && !empty($request->queryFields)) {
                $serializerFields = explode(',', $request->queryFields);
            }
            if ($request->includeFields) {
                $includeFields = explode(',', $request->includeFields);
                $serializerFields = array_merge($serializerFields, $includeFields);
            }
            if ($request->fixincludeFields && !empty($request->fixincludeFields)) {
                $serializerFields =  explode(',', $request->fixincludeFields);
            }
            if ($request->excludeFields) {
                $excludeFields = explode(',', $request->excludeFields);
                $serializerFields = array_diff($serializerFields, $excludeFields);
            }
            if (method_exists($this->entityInstance, 'serializerFields')) {
                BaseResource::serializerFieldsSet($serializerFields);
            }
            if (isset($request->filters)) {
                $result = $this->entityInstance->getResourceById($request, $id, $request->filters, $order_by, $order_by_field);
            } else {
                $result = $this->entityInstance->getResourceById($request, $id, null, $order_by, $order_by_field);
            }

            return (is_object(json_decode($result))) === false ?  $result : new BaseResource($result);
        } catch (Exception $ex) {
            return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return BaseResource
     */
    public function update(Request $request, $id)
    {
        $result = $this->entityInstance->updateResource($request, $id);
        return (is_object(json_decode($result))) === false ?  $result :  new BaseResource($result);
        try {
            $result = $this->entityInstance->updateResource($request, $id);
            return (is_object(json_decode($result))) === false ?  $result :  new BaseResource($result);
        } catch (Exception $ex) {
            return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return BaseResource|JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $result = $this->entityInstance->deleteResource($request, $id);
            return $result;
        } catch (Exception $ex) {
            return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], 200);
        }
    }
}
