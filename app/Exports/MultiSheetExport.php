<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;


class MultiSheetExport implements WithMultipleSheets
{
    use Exportable;

    protected $data;
    protected $names;

    public function __construct(array $data, array $names)
    {
        $this->data  = $data;
        $this->names = $names;
    }

    public function sheets(): array
    {   
        $sheets = [];
        foreach ($this->data as $index => $sheetData) {
            $sheets[] = new SSExport($sheetData, $this->names[$index]);
        }
        return $sheets;
    }
}
