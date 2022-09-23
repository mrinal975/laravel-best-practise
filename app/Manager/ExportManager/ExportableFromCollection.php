<?php

namespace App\Manager\ExportManager;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 3000);

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportableFromCollection implements FromArray, WithHeadings
{
    protected $list = [];
    protected $heading = [];

    public function __construct(array $data, array $heading)
    {
        $this->list = $data;
        $this->heading = $heading;
        if (!empty($this->heading)) {
            $this->heading;
        } else {
            $this->heading = ['id'];
        }
    }

    public function array(): array
    {
        return $this->list;
    }

    public function headings(): array
    {
        return $this->heading;
    }
}
