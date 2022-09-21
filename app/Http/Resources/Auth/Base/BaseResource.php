<?php

namespace App\Http\Resources\Base;

use App\Engine\HttpStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    private static $serializerFields = null;

    public static function serializerFieldsSet($fields)
    {
        self::$serializerFields = $fields;
    }

    public function fieldParse($field)
    {
        $field = explode('->', $field);
        $parts = explode('__', $field[0]);
        foreach ($parts as $key => $part) {
            if ($key == 0) {
                $relationField = $this->$part;
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

    public function getFieldKey($fullFieldName)
    {
        $key = explode('->', $fullFieldName);
        if (count($key) > 1) {
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
     * Transform the resource into an array.
     *
     * @param  Request
     * @return array
     */
    public function toArray($request)
    {
        $fields = array();
        if (self::$serializerFields) {
            foreach (self::$serializerFields as $field) {
                $fields[$this->getFieldKey($field)] = $this->fieldParse($field);
            }
        }
        if ($fields) {
            return ['data' => $fields, HttpStatus::STATUS => HttpStatus::OK];
        } else {
            return parent::toArray($request);
        }
    }
}
