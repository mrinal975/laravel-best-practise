<?php

namespace App\Manager\ExportManager;

use App\Utility\Algorithms\StringAlgorithm;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportableModelManager implements FromQuery, Responsable, WithMapping, WithHeadings, ShouldAutoSize
{
    use Exportable;
    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    private $fileName = 'random_name.xlsx';

    private $modelInstance;
    private $request;

    public function __construct($modelInstance, $querySet)
    {
        $this->modelInstance = $modelInstance;
        $this->querySet = $querySet;
    }

    public function query()
    {
        $withFields = $this->getWithFields();
        $querySet = $this->querySet->with($withFields);
        return $querySet;
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

    public function headings(): array
    {
        $header_list = [];
        $fieldList = $this->modelInstance->exportTableFields();
        foreach ($fieldList as $field) {
            $header_list[] = $this->getFieldKey($field);
        }
        return $header_list;
    }

    /**
     * Relationable field extra
     *
     * @param  array  $field
     * @return object
     */
    public function getValue($row, $field)
    {
        $field = explode('->', $field);
        $parts = explode('__', $field[0]);
        foreach ($parts as $key => $part) {
            if ($key == 0) {
                $relationField = $row->$part;
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

    public function map($row): array
    {
        $fieldList = $this->modelInstance->exportTableFields();
        $fields = [];
        foreach ($fieldList as $field) {
            $fields[] = $this->getValue($row, $field);
        }
        return $fields;
    }

    /**
     * @return Array
     */
    public function getWithFields(): array
    {
        $fieldList = $this->modelInstance->exportTableFields();
        $fields = [];
        foreach ($fieldList as $field) {
            if (StringAlgorithm::firstPatternMatching($field, '__')) {
                $_field = explode('__', $field)[0];
                $fields[] = $_field;
            }
        }
        return $fields;
    }
}
