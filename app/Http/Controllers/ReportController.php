<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\WialonController;

class ReportController extends Controller
{
    protected $wialonController;

    public function __construct(WialonController $wialonController){
        $this->wialonController = $wialonController;
    }
    
    function executeReportByRange(){
        $from = $_POST['from'];
        $to   = $_POST['to'];
        if(isset($from,$to) && ($from < $to)){
            $this->wialonController->getSessionEID();
            $this->wialonController->setTimeZone();
            $reportExecuteResult = $this->wialonController->executeReport($from,$to);
            if($reportExecuteResult['reportResult']['tables'][0]['rows']){
                $rows = (int)$reportExecuteResult['reportResult']['tables'][0]['rows'];
                if(isset($rows)){
                    $result = $this->wialonController->getRecords($rows);
                    print_r($result);
                    die();
                    if(!empty($result)){
                        $data = [];
                        foreach($result as $record){
                            $rec = [];
                            $rec['name'] = $record['c'][1];
                            $rec['date'] = $record['c'][4]['t'];
                        }
                    }
                }
            }
        }
    }


}
