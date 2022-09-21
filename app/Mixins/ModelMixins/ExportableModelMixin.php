<?php

namespace App\Mixins\ModelMixins;

use App\Engine\HttpStatus;
use App\Helpers\Helper;
use Maatwebsite\Excel\Facades\Excel;
use App\Manager\ExportManager\ExportableModelManager;
use App\Manager\ExportManager\ImportableModelManager;
use App\Models\Base\ImportFile;
use Illuminate\Support\Facades\Auth;

trait ExportableModelMixin
{
    public function export($request)
    {
        $fileName = basename(str_replace('\\', '/', get_class($this))).'_'.date("YmdHis");
        $orderbyField = 'id';
        $orderBy = 'asc';

        $querySet = $this->model::orderBy($this->tableName.'.'.$orderbyField, $orderBy)->where($this->tableName.'.deleted_at', null);
        
        if ($request->special_query) {
            $query_keys = explode(':', $request->special_query);
            if ($query_keys[0] == 'unique') {
                if (!$request->filter) {
                    $querySet = $this->joinTable($querySet);
                }
                $key = $this->getTableNameFromRelationKey($query_keys[1]);
                $querySet = $querySet->groupBy($key);
            }
        }
        if ($request->filters) {
            $querySet = $this->appliedMullipleFilter($querySet, $request->filters);
        }
        $exportable = new ExportableModelManager($this, $querySet);
        Excel::store($exportable, $fileName.'.xlsx', 'export');
        return Excel::download($exportable, $fileName.'.xlsx');
    }

    public function exportTableFields()
    {
        return['id'];
    }

    public function import($request)
    {
        Excel::import(new ImportableModelManager($this), $request->file('file'));
        
        $newImport = new ImportFile();
        $newImport->model = self::class;
        $newImport->file_path = (new Helper)->storeFile($request->file('file'), 'import');
        $newImport->url = $request->url();
        $newImport->name = $request->file('file')->getClientOriginalName();
        $newImport->extension = $request->file('file')->getClientOriginalExtension();
        $newImport->created_by = Auth::user()->id;
        $newImport->updated_by = Auth::user()->id;
        $newImport->company_id = Auth::user()->company_id;
        $newImport->save();

        return response(['data' => 'Import successfully done', HttpStatus::STATUS => HttpStatus::OK], HttpStatus::OK);
    }

    public function importableTableFields()
    {
        return['id'];
    }
}
