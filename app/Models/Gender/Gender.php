<?php

namespace App\Models\Gender;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class Gender extends BaseModel
{
    use HasFactory;
    public function __construct()
    {
        parent::__construct($this);
    }
    public function serializerFields()
    {
        return [
            'id', 'name', 'is_active', 'is_default', 'created_at'
        ];
    }

    static public function PostSerializerFields()
    {
        return [
            'id', 'name', 'is_active', 'is_default', 'created_at'
        ];
    }


    static public function FieldsValidator()
    {
        return [
            'name' => 'required',
        ];
    }


    public function exportTableFields()
    {
        return [
            'name', 'is_active'
        ];
    }
}
