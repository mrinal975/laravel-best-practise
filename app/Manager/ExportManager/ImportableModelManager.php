<?php

namespace App\Manager\ExportManager;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 3000);

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ImportableModelManager implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */

    private $modelInstance;
    private $fieldList;
    private $externalMethod = null;

    public function __construct($modelInstance, $methodName = null)
    {
        $this->modelInstance = $modelInstance;
        $this->fieldList = $this->modelInstance->importableTableFields();
        $this->externalMethod = $methodName;
    }

    /**
     * @param $newEntity
     * @return Array
     */
    private function getCommonFieldsValue(&$newEntity)
    {
        $userId = Auth::user()->id;
        $newEntity->created_by = $userId;
        $newEntity->updated_by = $userId;
        $newEntity->company_id = Auth::user()->company_id;
    }

    private function isDuplicateValue($field, $value): bool
    {
        if ($this->modelInstance->where($field, $value)->exists()) {
            return true;
        } else {
            return false;
        }
    }
    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        $foreignkeys = $this->modelInstance->getForeignKeys();
        $methodName = $this->externalMethod ? $this->externalMethod : 'importable';
        if (method_exists($this->modelInstance, $methodName)) {
            return $this->modelInstance->$methodName($rows);
        }
        foreach ($rows as $row) {
            if (isset($row['id']) && $row['id']) {
                $this->modelInstance->where('id', $row['id'])->update($row->toArray());
            } else {
                unset($row['id']);
                $newEntity = new $this->modelInstance();
                foreach ($this->fieldList as $field) {
                    if ($field == 'id') {
                        continue;
                    }
                    if (in_array($field, $foreignkeys)) {
                    } else {
                        $newEntity->$field = $row[$field];
                    }
                }
                $this->getCommonFieldsValue($newEntity);
                $newEntity->save();
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
