<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;


class MultiSheetExport implements WithMultipleSheets
{
    use Exportable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {   
        // $sheets = [];
        // foreach($this->data as $sheet){
        //     $sheets[] = new SSExport($sheet,'');
        // }
        // return $sheets;
        return [
            'First Sheet' => new SSExport($this->data[0],''),
            'Second Sheet' => new SSExport($this->data[1],''),
        ];
    }
}
