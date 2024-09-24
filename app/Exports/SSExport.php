<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

// class SSExport implements FromArray,WithHeadings, WithEvents, ShouldAutoSize
// {
//     protected $data;

//     public function __construct(array $data)
//     {
//         $this->data = $data;
//     }

//     public function array(): array
//     {
//         // return $this->data;
//         $formattedData = [];
//         foreach ($this->data as $row) {
            
//             // Use the first two elements and then flatten the sub-array dynamically
//             $formattedRow = [
//                 $row[0], // Company Name
//                 $row[1], // Company Location
//             ];

//             // Append the dynamic sub-array values
//             if (is_array($row[2])) {
//                 $formattedRow = array_merge($formattedRow, $row[2]);
//             }

//             // Add the total visits
//             $formattedRow[] = $row[3]; // Total Visit

//             $formattedData[] = $formattedRow;
//         }
//         return $formattedData;
//     }

//     public function headings(): array
//     {
//         // Dynamically create headings based on the max number of sub-columns
//         $maxVisits = max(array_map(function ($row) {
//             return count($row[2]);
//         }, $this->data));

    
//         // Prepare the headings
//         $headings = ['Company Name', 'Company Location'];

//         // Add dynamic visit date headings
//         for ($i = 1; $i <= $maxVisits; $i++) {
//             $headings[] = "Visit Date";
//         }

//         // Add total visits heading
//         $headings[] = 'Total Visit';

//         return [$headings];
//     }

//     // Merge cells and apply styles
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet = $event->sheet->getDelegate();

//                 // Merge cells for the 'Visit Date' header if applicable
//                 // Calculate the range to merge based on the number of visit date columns
//                 $maxVisits = max(array_map(function ($row) {
//                     return count($row[2]);
//                 }, $this->data));

//                 if ($maxVisits > 0) {
//                     $sheet->mergeCells('C1:' . $this->getExcelColumnLetter(2 + $maxVisits) . '1'); // Merges cells for Visit Date header
//                 }

//                 // Center alignment and bold styling for merged cells
//                 $sheet->getStyle('A1:' . $this->getExcelColumnLetter(2 + $maxVisits) . '1')->applyFromArray([
//                     'font' => [
//                         'bold' => true,
//                     ],
//                     'alignment' => [
//                         'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
//                         'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
//                     ],
//                 ]);

//                 // Adjust cell height for merged header row
//                 $sheet->getRowDimension(1)->setRowHeight(30);
//             }
//         ];
//     }

//     function getExcelColumnLetter($columnNumber)
//     {
//         $columnLetter = '';
//         while ($columnNumber > 0) {
//             $remainder = ($columnNumber - 1) % 26;
//             $columnLetter = chr(65 + $remainder) . $columnLetter;
//             $columnNumber = (int)(($columnNumber - $remainder) / 26);
//         }
//         return $columnLetter;
//     }
// }

class SSExport implements FromArray, WithHeadings, WithEvents, ShouldAutoSize, WithTitle
{
    protected $data;
    protected $title;

    public function __construct(array $data,string $title)
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function array(): array
    {
        $formattedData = [];
        foreach ($this->data as $row) {
            // Start with company name and location
            $formattedRow = [
                $row[0], // Company Name
                $row[1], // Company Location
            ];

            // Append the dynamic sub-array values for Visit Dates
            if (is_array($row[2])) {
                $formattedRow = array_merge($formattedRow, $row[2]);
            }

            // Initialize the total visits with the correct position
            $totalVisits = $row[3]; // Total Visit
            
            // Fill the remaining slots with empty strings if there are fewer visit dates than max
            $maxVisits = max(array_map(function ($row) {
                return count($row[2]);
            }, $this->data));
            $visitCount = count($row[2]);
            if ($visitCount < $maxVisits) {
                $formattedRow = array_merge($formattedRow, array_fill(0, $maxVisits - $visitCount, ''));
            }

            // Add the total visits at the end
            $formattedRow[] = $totalVisits; // Total Visit

            $formattedData[] = $formattedRow;
        }
        return $formattedData;
    }

    public function headings(): array
    {
        // Determine the max number of visit dates across all rows
        $maxVisits = max(array_map(function ($row) {
            return count($row[2]);
        }, $this->data));

        // Prepare the headings
        $headings = ['Company Name', 'Company Location', 'Visit Date'];

        // Add dynamic visit date sub-headings based on maxVisits
        for ($i = 1; $i < $maxVisits; $i++) {
            $headings[] = "Visit Date $i";
        }

        // Add total visits heading
        $headings[] = 'Total Visit';

        return [$headings];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge cells for the 'Visit Date' header
                $maxVisits = max(array_map(function ($row) {
                    return count($row[2]);
                }, $this->data));

                // Merge the 'Visit Date' cell
                if ($maxVisits > 0) {
                    $sheet->mergeCells('C1:' . $this->getExcelColumnLetter(2 + $maxVisits) . '1'); // Merges cells for Visit Date header
                }

                // Center alignment and bold styling for merged cells
                $sheet->getStyle('A1:' . $this->getExcelColumnLetter(2 + $maxVisits) . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Adjust cell height for merged header row
                $sheet->getRowDimension(1)->setRowHeight(30);
            }
        ];
    }

    protected function getExcelColumnLetter($columnNumber)
    {
        $columnLetter = '';
        while ($columnNumber > 0) {
            $remainder = ($columnNumber - 1) % 26;
            $columnLetter = chr(65 + $remainder) . $columnLetter;
            $columnNumber = (int)(($columnNumber - $remainder) / 26);
        }
        return $columnLetter;
    }
    
    public function title(): string
    {
        return $this->title;
    }
}