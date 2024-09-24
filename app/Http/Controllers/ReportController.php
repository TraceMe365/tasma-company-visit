<?php

namespace App\Http\Controllers;

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
    
    function executeReportByRange(){
        return Excel::download(new UsersExport, 'users.xlsx');
        die();
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
                                $rec['locations']['location_name'][] = $loc['c'][2]; 
                                $rec['locations']['location_date'][] = $loc['c'][4]['t']; 
                            }
                            array_push($data,$rec);
                        }
                        if(!empty($data)){
                            // Create Excel
                        }
                    }
                }
            }
        }
    }


}
