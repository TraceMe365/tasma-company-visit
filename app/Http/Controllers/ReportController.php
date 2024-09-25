<?php

namespace App\Http\Controllers;

use App\Exports\MultiSheetExport;
use App\Exports\SSExport;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use App\Http\Controllers\WialonController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
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
                                $rec['locations']['location_name'][]    = $loc['c'][2];
                                $rec['locations']['location_address'][] = $loc['c'][3];
                                $rec['locations']['location_date'][]    = $loc['c'][4]['t'];
                                $rec['locations']['location_visits'][]  = $record['c'][5];
                            }
                            array_push($data,$rec);
                        }
                        if(!empty($data)){
                            $files = [];
                            $names = [];
                            foreach($data as $array){
                                // Create Excel
                                $files[] = $this->createFormattedArray($array,$fromHuman,$toHuman);
                                $names[]  = $array['name'];
                            }
                            $time = Carbon::now()->timestamp;
                            $filePath = "Company-Visit-Summary-$time.xlsx";
                            Excel::store(new MultiSheetExport($files,$names), $filePath, 'public');
                            return response()->json([
                                'fileUrl' => env('APP_URL') . '/storage/' . $filePath
                            ]);
                        }
                    }
                }
            }
        }
    }

    function createFormattedArray($array,$from,$to)
    {
        
        $days     = $this->getDaysInBetween($from,$to);
        $allRows  = [];
        $firstRow = ['','',$days,''];
        array_push($allRows,$firstRow);
        
        for ($i = 0; $i < count($array['locations']['location_name']); $i++) {
            $row = [
                $array['locations']['location_name'][$i], 
                $array['locations']['location_address'][$i],
                [$array['locations']['location_date'][$i]],
                $array['locations']['location_visits'][$i], 
            ];
            array_push($allRows, $row);
        }
        
        // Remove timestamp from record
        foreach($allRows as $index=>&$or){
            // Skip Header
            if($index>0){
                foreach($or[2] as &$dt){
                    $dt = explode(' ', $dt)[0];
                }
            }
        }

        // Formatting Dates
        $availableDates = $allRows[0][2];
        foreach ($allRows as $index => &$rows) {
            if ($index === 0) {
                continue;
            }
            // Get the dates from the second array
            $datesInSecondArray = $rows[2];
            $newDates = [];
            foreach ($availableDates as $date) {
                if (in_array($date, $datesInSecondArray)) {
                    $newDates[] = $date;
                } else {
                    $newDates[] = ""; 
                }
            }        
            $rows[2] = $newDates;
        }

        // Merge duplicate location rows
        $mergedLocations = [];

        // Iterate through each location entry
        foreach ($allRows as $location) {
            $locationName = $location[0];  // Extract the location name
            
            // Check if this location already exists in the merged array
            if (isset($mergedLocations[$locationName])) {
                // Merge date arrays
                foreach ($location[2] as $index => $date) {
                    if (!empty($date)) {
                        $mergedLocations[$locationName][2][$index] = $date;
                    }
                }
            } else {
                // If not already in the merged array, add the entire location entry
                $mergedLocations[$locationName] = $location;
            }
        }

        // Convert back to a normal indexed array if needed
        $mergedLocations = array_values($mergedLocations);

        // Count Visits Per Location
        foreach($mergedLocations as $ind=>&$loc){
            if($ind>0){
                $loc[3] = count(array_filter($loc[2]));
            }
        }
       
        return $mergedLocations;
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

    function flattenArray($nestedArray) {
        $flattened = [];
    
        foreach ($nestedArray as $row) {
            foreach ($row as $subRow) {
                // Check if it's an array
                if (is_array($subRow)) {
                    // Merge sub-row into the flattened array
                    $flattened[] = array_merge($subRow);
                } else {
                    // Add non-array elements directly
                    $flattened[] = $subRow;
                }
            }
        }
    
        return $flattened;
    }


}
