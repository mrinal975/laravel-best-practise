<?php

namespace App\Http\Resources\Base;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomResource extends JsonResource
{
    private $serializerFields = array();

    public function getFields($fields)
    {
        $this->serializerFields = $fields;
    }

    public function fieldParse($field)
    {
        $parts = explode('__', $field);
        foreach ($parts as $key => $part) {
            if ($key == 0) {
                $relationField = $this->$part;
            } else {
                $relationField = $relationField->$part;
            }
        }
        return $relationField;
    }

    public function getFieldKey($fullFieldName)
    {
        $keys = explode('__', $fullFieldName);
        return $keys[0];
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
            foreach ($this->serializerFields as $field) {
                $fields[$this->getFieldKey($field)] = $this->fieldParse($field);
            }
        }
        if ($fields) {
            return $fields;
        } else {
            return parent::toArray($request);
        }
    }
}
