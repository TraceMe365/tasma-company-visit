<?php

namespace App\Http\Controllers;

use App\Exports\SSExport;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use App\Http\Controllers\WialonController;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected $wialonController;

    public function __construct(WialonController $wialonController){
        $this->wialonController = $wialonController;
    }

    function exportUsers(){
        // return Excel::download(new UsersExport, 'users.xlsx');
        
        // $export = new SSExport([
        //     ['','',[1,2,3,4,5,6,7,8,9,10],''],
        //     ['Ceylon Petroleum Storage', 'Kollonawa', [1, 2, 3, 4], 10],
        //     ['Company XYZ', 'Location ABC', [5, 6], 8],
        //     ['Company 123', 'Location DEF', [7, 8, 9], 5],
        //     ['Company 123', 'Location DEF', [7, 8, 9,10,13,14], 5],
        // ]);
        // return Excel::download($export, 'invoices.xlsx');
        
        $from     = '2024-09-10';
        $to       = '2024-09-15';
        $days     = $this->getDaysInBetween($from,$to);
        $firstRow = ['','',$days,''];
        $export   = new SSExport([
            $firstRow,
            ['Ceylon Petroleum Storage', 'Kollonawa', [1, 2, 3, 4], 10]
        ],'Megatron');
        return Excel::download($export, 'invoices.xlsx');

    }
    
    function executeReportByRange(){
        $from      = $_POST['from'];
        $to        = $_POST['to'];
        $fromHuman = $_POST['from_human'];
        $toHuman   = $_POST['to_human'];

        // ----------------------------------------------------------------
        $days     = $this->getDaysInBetween($fromHuman,$toHuman);
        $firstRow = ['','',$days,''];
        $export   = new SSExport([
            $firstRow,
            ['Ceylon Petroleum Storage', 'Kollonawa', [1, 2, 3, 4], 10]
        ],'Megatron');
        return Excel::download($export, 'Company Visit Summary.xlsx');
        // -----------------------------------------------------------------

        if(isset($from,$to) && ($from < $to)){
            $this->wialonController->getSessionEID();
            $this->wialonController->setTimeZone();
            $reportExecuteResult = $this->wialonController->executeReport($from,$to);
            if($reportExecuteResult['reportResult']['tables'][0]['rows']){
                $rows = (int)$reportExecuteResult['reportResult']['tables'][0]['rows'];
                if(isset($rows)){
                    $result = $this->wialonController->getRecords($rows);
                    if(!empty($result)){
                        $data = [];
                        foreach($result as $record){
                            $rec = [];
                            $rec['name'] = $record['c'][1];
                            $rec['date'] = $record['c'][4]['t'];
                            foreach($record['r'] as $loc){
                                $rec['locations']['location_name'][] = $loc['c'][2]; 
                                $rec['locations']['location_date'][] = $loc['c'][4]['t']; 
                            }
                            array_push($data,$rec);
                        }
                        if(!empty($data)){
                            // Create Excel
                            $formatted = $this->createFormattedArray($data,$fromHuman,$toHuman);
                        }
                    }
                }
            }
        }
    }

    function createFormattedArray($array,$from,$to)
    {
        $days = $this->getDaysInBetween($from,$to);
        $firstRow = ['','',$days,''];
        
    }

    function getDaysInBetween($from,$to)
    {
        $startDate = new \DateTime($from);
        $endDate = new \DateTime($to);

        // Create an interval of 1 day
        $interval = new \DateInterval('P1D');

        // Create a DatePeriod to get all dates between start and end
        $datePeriod = new \DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        // Initialize an array to hold the dates
        $datesBetween = [];

        // Loop through the DatePeriod and add dates to the array
        foreach ($datePeriod as $date) {
            $datesBetween[] = $date->format('Y-m-d');
        }

        // Output the results
        return ($datesBetween);

    }


}
