<?php

namespace App\Mixins\ControllerMixins;

use App\Engine\HttpStatus;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Base\BaseResource;

trait ProtectedBaseController
{
    protected $per_page = 1000;
    /**
     * Update the a list of resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return BaseResource
     */
    public function bulkUpdate(Request $request)
    {
        try {
            $result = $this->entityInstance->bulkUpateResource($request);
            return (is_object(json_decode($result))) === false ?  $result :  new BaseResource($result);
        } catch (Exception $ex) {
            Log::error($ex);
            return response(['error' => true], 500);
        }
    }

    /**
     * Export Resource
     *
     * @param Request $request
     * @return DataFile
     */
    public function export(Request $request)
    {
        return $this->entityInstance->export($request);
        //  try {
        //     return $this->entityInstance->export($request);
        //  } catch(Exception $ex) {
        //      Log::error($ex);
        //      return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], HttpStatus::OK);
        //  }
    }

    /**
     * Import Resource
     *
     * @param Request $request
     * @return DataFile
     */
    public function import(Request $request)
    {
        try {
            return $this->entityInstance->import($request);
        } catch (Exception $ex) {
            return $ex;
            Log::error($ex);
            return response([HttpStatus::STATUS => HttpStatus::INTERNAL_SERVER_ERROR], HttpStatus::OK);
        }
    }
}
