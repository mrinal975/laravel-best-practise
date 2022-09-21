<?php

namespace App\Http\Resources\Base;

use App\Engine\HttpStatus;
use App\Utility\Algorithms\StringAlgorithm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseCollection extends ResourceCollection
{
    private static $serializerFields = null;
    private $modelInstance = null;


    /**
    * Create a new resource instance.
    *
    * @param  mixed  $modelInstance
    * @return void
    */

    public function getEntityModelInstance($modelInstance)
    {
        $this->modelInstance = $modelInstance;
    }


    public static function serializerFieldsSet($fields)
    {
        self::$serializerFields = $fields;
    }

    /**
    * Relationable field extra
    *
    * @param  array  $field
    * @return object
    */
    public function fieldParse($instance, $field)
    {
        $field = explode('->', $field);
        $parts = explode('__', $field[0]);
        foreach ($parts as $key => $part) {
            if ($key == 0) {
                $relationField = $instance->$part;
            } else {
                if (isset($relationField->$part)) {
                    $relationField = $relationField->$part;
                } else {
                    return null;
                }
            }
        }
        return $relationField;
    }

    /**
     * exac field return
     *
     * @param  string  $field
     * @return string
     */
    public function getFieldKey($fullFieldName)
    {
        $key = explode('->', $fullFieldName);
        if (count($key)>1) {
            return $key[1];
        }
        $keys = explode('__', $fullFieldName);
        $field = '';
        foreach ($keys as $key) {
            $field .= $key.'_';
        }
        return substr($field, 0, strlen($field)-1);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function ($item) {
                $finalField = array();
                if (self::$serializerFields) {
                    foreach (self::$serializerFields as $field) {
                        if (StringAlgorithm::firstPatternMatching($field, '__')) {
                            $exact_field = explode('__', $field)[0];
                            if (in_array($exact_field, self::$serializerFields)) {
                                $this->fieldParse($item, $field);
                            } else {
                                $finalField[$this->getFieldKey($field)] = $this->fieldParse($item, $field);
                            }
                        } else {
                            $finalField[$this->getFieldKey($field)] = $this->fieldParse($item, $field);
                        }
                    }
                }
                return $finalField;
            }),
            HttpStatus::STATUS => HttpStatus::OK
        ];
    }
}
