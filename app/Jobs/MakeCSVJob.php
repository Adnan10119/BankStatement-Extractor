<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\SignRequest;
use App\Models\History;
use DateTime;
use App\Models\MakeXLSX;
use Excel;
use App\Exports\XLSXExport;
use Carbon\Carbon;

class MakeCSVJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $url;
    private $filename;
    private $bankInfo;
    private $id;
    private $user_id;
    private $country;
    private $finalCsvArray;
    private $finalCsvHeaderArray;
    private $isSummary;
    private $summary;
    private $header;
    private $currentType;
    private $previousType;
    private $balanceCsvArray;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($post)
    {
        $this->url = $post['cropUrl'];
        $this->filename = $post['filename'];
        $this->bankInfo = $post['bankInfo'];
        $this->id = $post['recordId'];
        $this->user_id = $post['user_id'];
        $this->country = $post['country'];
        $this->finalCsvArray = [];
        $this->balanceCsvArray = [];
        $this->finalCsvHeaderArray = [];
        $this->isSummary = false;
        $this->summary = [];
        $this->header = "";
        $this->currentType = "";
        $this->previousType = "";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $history = [
                'status' => 55
            ];
            History::where('id',$this->id)->update($history);
            $fileRecord = History::where('id',$this->id)->first();
            $fileName = $fileRecord->output_file_id;
            foreach ($this->url as $key3 => $value) {
                \Log::info('fetching csv files at page '.$value['page_no']." and table ".$value['table_no']." ");
                $this->fetchCsv($value);
                if(in_array($this->currentType,[0,4,5,6])){
                    $this->previousType = $this->currentType;
                }
            }
            $history = [
                'status' => 80
            ];
            History::where('id',$this->id)->update($history);
            $bankInfo = $this->bankInfo();
            $finalArray = [];
            $finalBalanceArray = [];
            $finalArray = $this->parseArrayForDateValidation($this->finalCsvArray, $bankInfo[4]);
            $finalBalanceArray = $this->parseArrayForDateValidation($this->balanceCsvArray, $bankInfo[4]);

            $sizeOfArray = sizeOf($this->finalCsvArray);
            \Log::Debug("size of final this array : ".$sizeOfArray);
            $sizeOfArray = sizeOf($this->balanceCsvArray);
            \Log::Debug("size of final balanceCsvArray this array : ".$sizeOfArray);

            $endpoint = "/balance";
            $base = "7cfgx5o3b3.execute-api.us-west-2.amazonaws.com";
            // $Url = "https://yped58pfhl.execute-api.us-west-2.amazonaws.com/year_rolling";
            $Url = "https://7cfgx5o3b3.execute-api.us-west-2.amazonaws.com/balance";
            $post = [
                "year" => $bankInfo[4],
                "body" => $finalArray,
                "balance" => $finalBalanceArray,
            ];
            $result = \App\Traits\ModelAPITrait::index($post, $base, $endpoint, $Url);
            if(isset($result['statusCode']) && $result['statusCode'] == 200)
            {
                \Log::Debug($result);
                $this->finalCsvArray = $result["body"];
            }
            else{
                \Log::Debug($result);
                $history = [
                    'status' => 102,
                    'flag' => 0
                ];
                History::where('id',$this->id)->update($history);
                return;
            }
            $filePath = public_path().'/assets/output/'.$fileName;
            if(file_exists($filePath)){
                unlink($filePath);
            }

            // usort($body, function ($date1, $date2){
            //     \Log::info("start comparing date");
            //     \Log::info($date1[4]);
            //     \Log::info($date2[4]);
            //     \Log::info("comparing date");
            //     if((string)$date1[4] == (string)$date2[4]){
            //         return 0;
            //     }
            //     return new DateTime($date1[4]) <=> new DateTime($date2[4]);
            // });

            $index = 0;
            MakeXLSX::where('user_id',$this->user_id)->delete();
            $statementDate = "";

            $sizeOfArray = sizeOf($this->finalCsvArray);
            \Log::Debug("size of final array : ".$sizeOfArray);

            \Log::Debug($this->finalCsvArray);
            $isSummaryFind = false;
            if(isset($this->summary['BEGINNING_BALANCE']) && !empty($this->summary['BEGINNING_BALANCE'])){
                $isSummaryFind = true;
                $balance = $this->summary['BEGINNING_BALANCE'];
                //here 
                $index = $index + 1;
                $dataScript = [
                    'user_id' => $this->user_id,
                    'index' => $index,
                    'account_name' => $bankInfo[0],
                    'account_number' => $bankInfo[1],
                    'account_type' => $bankInfo[2],
                    'ck' => $bankInfo[3],
                    'date' => '',
                    'description' => 'Beginning Balance',
                    'debit' => '',
                    'credit' => '',
                    'value' => '',
                    'balance' => $this->removeSpecialChar($balance),
                    'statement_balance' => $this->removeSpecialChar($balance),
                    'difference' => '',
                ];
                MakeXLSX::insert($dataScript);
                if(isset($this->finalCsvArray[0][0])){
                    $start_Date = $this->finalCsvArray[0][0];//. "/" .$bankInfo[4];
                    $start_Date = str_replace('-','/',$start_Date);
                    $statementDate = $start_Date . " - ";
                }
            }
            foreach ($this->finalCsvArray as $key => $data)
            {
                if(empty($data[0])){
                    \Log::Debug("date not present");
                    continue;
                }
                // else{
                //     // $date = $this->DateFormat($data[0],$bankInfo[4]);
                //     $date = $data[0];
                //     \Log::Debug("date : ".$date);
                //     if(preg_match("/[a-zA-Z]/i", $date) || strlen($date) < 8){
                //         \Log::Debug("date improper format present");
                //         continue;
                //     }
                //     // $data[0] = $date;
                // }
                $index = $index + 1;
                $dataScript = [
                    'user_id' => $this->user_id,
                    'index' => $index,
                    'account_name' => $bankInfo[0],
                    'account_number' => $bankInfo[1],
                    'account_type' => $bankInfo[2],
                    'ck' => isset($data[1])?$data[1]:$bankInfo[3],
                    'date' => isset($data[0])?$data[0]:'',
                    'description' => isset($data[3])?$data[3]:'',
                    'debit' => isset($data[4])?$this->testCredit($data[4]):'',
                    'credit' => isset($data[5])?$this->testCredit($data[5]):'',
                    'value' => '',
                    'balance' => '',
                    'statement_balance' => isset($data[7])?$this->removeSpecialChar($data[7]):'',
                    'difference' => '',
                ];
                if($index == 1){
                    $statementDate = $data[0] . " - ";
                    $balance = 0;
                    if(isset($data[7]) && !empty($data[7])){
                        $balance = $data[7];
                    }
                    else if(isset($data[6]) && !empty($data[6])){
                        $balance = $data[6];
                    }
                    $dataScript['balance'] = floatval($this->removeSpecialChar($balance));
                }
                if($index == $sizeOfArray){
                    $statementDate .= $data[0];
                    $history = [
                        'time_period' => $statementDate
                    ];
                    History::where('id',$this->id)->update($history);
                }
                $description = strtolower($dataScript['description']);
                if($key == 0 && $isSummaryFind == false){
                    MakeXLSX::insert($dataScript);
                }
                else if($key == 0 &&  $isSummaryFind == true && $this->excludeString($description)){
                    MakeXLSX::insert($dataScript);
                }
                else if($key != 0 && $this->excludeString($description)){
                    MakeXLSX::insert($dataScript);
                }
                else{
                    $index = $index - 1;
                }
            }
            Excel::store(new XLSXExport($this->user_id), $fileName,'public_uploads_output', \Maatwebsite\Excel\Excel::XLSX);
            $pathToFile = 'output/' . $fileName;
            $storage_file = \Storage::disk('public_uploads_output')->get($fileName);
            $s3 = \Storage::disk('s3')->put($pathToFile, $storage_file);
            unlink(public_path().'/assets/output/'.$fileName);
            $history = [
                'status' => 100,
                'flag' => 0
            ];
            History::where('id',$this->id)->update($history);
        }
        catch(\Exception $e){
            \Log::info('job failed');
            \Log::info($e->getMessage());
            \Log::info($e->getLine());
            \Log::info($e);
            $history = [
                'status' => 102,
                'flag' => 0
            ];
            History::where('id',$this->id)->update($history);
        }
    }

    public function parseArrayForDateValidation($array,$year){
        $finalArray = [];
        foreach ($array as $key => $data)
        {
            if(empty($data[0])){
                \Log::Debug("date not present");
                continue;
            }
            // else{
            //     $date = $this->DateFormat($data[0],$year);
            //     \Log::Debug("date : ".$date);
            //     if(preg_match("/[a-zA-Z]/i", $date) || strlen($date) < 8){
            //         \Log::Debug("date improper format present");
            //         continue;
            //     }
            //     $data[0] = $date;
            // }
            $finalArray[] = $data;
        }
        return $finalArray;
    }

    public function excludeString($description){
        $description = trim($description);
        if($description == 'beginning balance'){
            return false;
        }
        elseif($description == 'ending balance'){
            return false;
        }
        elseif($description == 'balance last statement'){
            return false;
        }
        elseif($description == 'balance forward'){
            return false;
        }
        else{
            return true;
        }
    }

    public function fetchCsv($value)
    {
        try{
            $this->headerReCheckApi = false;
            $this->current_page = $value['page_no'];
            $this->current_table = $value['table_no'];
            $this->currentType = $value['type'];
            \Log::Debug("Current type : ".$this->currentType);
            \Log::Debug("Previous type : ".$this->previousType);
            if($value['type'] == 1){
                if($this->isSummary == false){
                    \Log::debug("summary starts here");
                    $this->summary = $this->SummarryApi($value['url']);
                    \Log::debug($this->summary);
                }
            }
            else if( in_array($this->currentType,[2,3]) && in_array($this->previousType,[1,0,6]) )
            {
                return;
            }
            else if( in_array($this->currentType,[2,3]) && in_array($this->previousType,[2,3]) )
            {
                return;
            }
            else// if((int)$value['type'] == 0 || (int)$value['type'] == 4 || (int)$value['type'] == 5 || (int)$value['type'] == 6)
            {
                
                $endpoint = "/prod";
                $base = "ndll5wvdf6.execute-api.us-west-2.amazonaws.com";
                $Url = "https://ndll5wvdf6.execute-api.us-west-2.amazonaws.com/prod";
                $post = [
                    "url" => $value['url'],
                    "type" => (int)$value['type'],
                    "header" => $this->header,
                    "country" => $this->country,
                    "page_url" => $value['page_url']
                ];
                $response = \App\Traits\ModelAPITrait::index($post, $base, $endpoint, $Url);
                $CsvArray = [];
                if(isset($response['statusCode']) && $response['statusCode'] == 200)
                {
                    \Log::Debug("response from API of CSV");
                    \Log::debug($response['body']);
                    \Log::Debug("response from API of CSV END here");
                    $bodyArray = $response['body'];
                    if(isset($response['header'])){
                        $this->header = $response['header'];
                    }
                    if($this->currentType == 2){
                        $this->balanceCsvArray = array_merge($this->balanceCsvArray, $bodyArray);
                    }
                    else{
                        $this->finalCsvArray = array_merge($this->finalCsvArray, $bodyArray);
                    }
                }
                else{
                    \Log::Debug("response from API of CSV");
                    \Log::debug($response['body']);
                    \Log::Debug("response from API of CSV END here");
                    $CsvArray = [
                        'type' => $value['type'],
                        'error' => " Model failed while extracting text at page ".$value['page_no']." and table ".$value['table_no']." ",
                    ];
                    \Log::debug($CsvArray);
                    $error = " Model failed while extracting text at page ".$value['page_no']." and table ".$value['table_no']." ";

                    $tempArray =  array (
                        0 => '',
                        1 => '',
                        2 => '',
                        3 => '',
                        4 => '',
                        5 => $error,
                        6 => '',
                        7 => '',
                        8 => '',
                        9 => '',
                    );
                    array_push($this->finalCsvArray,$tempArray);
                    \Log::debug($error);
                }
                return true;
            }
        }
        catch(\Exception $e){
            $error = " System failed while formating current row at page ".$this->current_page." and table ".$this->current_table." ";

            $tempArray =  array (
                0 => '',
                1 => '',
                2 => '',
                3 => '',
                4 => '',
                5 => $error,
                6 => '',
                7 => '',
                8 => '',
                9 => '',
            );
            array_push($this->finalCsvArray,$tempArray);
            \Log::debug($error);
            \Log::debug($e->getMessage());
            \Log::debug(" at line = ".$e->getLine());
        }
    }
    public function SummarryApi($url){
        $this->isSummary = true;
        $endpoint = "/prod";
        $base = "f9fhd7hv3a.execute-api.us-west-2.amazonaws.com";
        $Url = "https://f9fhd7hv3a.execute-api.us-west-2.amazonaws.com/prod";
        $post = [
            "URL" => $url
        ];
        $response = \App\Traits\ModelAPITrait::index($post, $base, $endpoint, $Url);
        $CsvArray = [];
        $summary = '';
        if(isset($response['statusCode']) && $response['statusCode'] == 200){
            $summary = $response['body'];
        }
        else{
            $summary = null;
        }
        return $summary;
    }

    public function bankInfo(){
        $extractYear = '';
        $bankInfo = $this->bankInfo;
        if($bankInfo != null){
            if(!empty($bankInfo['Bank_ST_ENDING_DATE'])){
                $bankInfoDate = str_replace("-", "/", $bankInfo['Bank_ST_ENDING_DATE']);
                $bankInfoDate = str_replace(",", "/", $bankInfoDate);
                $bankInfoDate = str_replace(" ", "/", $bankInfoDate);
                $TempextractYear = substr($bankInfoDate, strrpos($bankInfoDate, '/') + 1);
                if(strlen($TempextractYear) < 3){
                    $extractYear .= 20 . $TempextractYear;
                }
                else{
                    $extractYear = $TempextractYear;
                }
            }
            else{
                $bankInfoDate = str_replace("-", "/", $bankInfo['Bank_ST_DATE']);
                $bankInfoDate = str_replace(",", "/", $bankInfoDate);
                $bankInfoDate = str_replace(" ", "/", $bankInfoDate);
                $TempextractYear = substr($bankInfoDate, strrpos($bankInfoDate, '/') + 1);
                if(strlen($TempextractYear) < 3){
                    $extractYear .= 20 . $TempextractYear;
                }
                else{
                    $extractYear = $TempextractYear;
                }
            }
            $bankOwnerName = $bankInfo['Bank_ST_ACT_OWNER_NAME'];
            $bankOwnerName1 = explode(',',$bankInfo['Bank_ST_ACT_OWNER_NAME']);
            if(isset($bankOwnerName1[0]) && isset($bankOwnerName1[1])){
                if(trim($bankOwnerName1[0]) == trim($bankOwnerName1[1])){
                    $bankOwnerName = $bankOwnerName1[0];
                }
            }
            if(str_contains(strtolower(trim($bankInfo['Bank_ST_ACT_OWNER_NAME'])), 'office')){
                $bankOwnerName = $bankOwnerName1[0];
            }
            $tempArray = [];
            $tempArray[0] = $bankOwnerName;
            $tempArray[1] = $bankInfo['Bank_ST_ACT_NMBR'];
            $tempArray[2] = isset($bankInfo['ACCOUNT_TYPE'])?$bankInfo['ACCOUNT_TYPE']:"";
            $tempArray[3] = '';
            $tempArray[4] = $extractYear;
        }
        else{
            $tempArray = [];
            $tempArray[0] = "";
            $tempArray[1] = "";
            $tempArray[2] = "";
            $tempArray[3] = "";
            $tempArray[4] = "";
        }
        return $tempArray;
    }

    public function removeSpecialChar($value){
        $value = str_replace("$", "", $value);
        $value = str_replace(",", "", $value);
        $value = str_replace(":", "", $value);
        // $value = str_replace("-", "", $value);
        // $value = str_replace("+", "", $value);
        $value = str_replace(" ", "", $value);
        $value = trim($value);
        if(!empty($value)){
            $value = floatval($value);
        }
        return $value;
    }

    public function DateFormat($date,$extractYear){
        $date = trim($date);
        $month_Dict = [];
        $month_Dict['jan'] = "01";
        $month_Dict['feb'] = "02";
        $month_Dict['mar'] = "03";
        $month_Dict['apr'] = "04";
        $month_Dict['may'] = "05";
        $month_Dict['jun'] = "06";
        $month_Dict['jul'] = "07";
        $month_Dict['aug'] = "08";
        $month_Dict['sep'] = "09";
        $month_Dict['oct'] = "10";
        $month_Dict['nov'] = "11";
        $month_Dict['dec'] = "12";
        $monthArray = [];
        $monthArray[] = "jan";
        $monthArray[] = "feb";
        $monthArray[] = "mar";
        $monthArray[] = "apr";
        $monthArray[] = "may";
        $monthArray[] = "jun";
        $monthArray[] = "jul";
        $monthArray[] = "aug";
        $monthArray[] = "sep";
        $monthArray[] = "oct";
        $monthArray[] = "nov";
        $monthArray[] = "dec";
        $date = str_replace("-", "/", $date);
        $formatedDate = '';
        $date = explode('/',$date);
        $index1 = false;
        $index2 = false;
        \Log::Debug($date);
        if(isset($date[0]) && isset($date[1])){

            if($date[0] <= 12 && strlen($date[1]) <= 2){
                $index1 = true;
            }
            else if($date[1] <= 12 || strlen($date[1]) > 2){
                $index2 = true;
            }

            if($index1 == true){
                //month at index 0
                \Log::Debug("Index 1 true");
                foreach($monthArray as $month){
                    if(str_contains(strtolower(trim($date[0])), $month)){
                        $date[0] = $month_Dict[strtolower($date[0])];
                    }
                }
                \Log::Debug("Got month: ".$date[0]);
                if(strlen($date[0]) < 2){
                    $formatedDate .= '0' . $date[0];
                }
                else{
                    $formatedDate .= $date[0];
                }
            }
            else if($index2 == true){
                //month at index 1
                \Log::Debug("Index 2 true");
                foreach($monthArray as $month){
                    if(str_contains(strtolower(trim($date[1])), $month)){
                        $date[1] = $month_Dict[strtolower($date[1])];
                    }
                }
                \Log::Debug("Got month: ".$date[1]);
                if(strlen($date[1]) < 2){
                    $formatedDate .= '0' . $date[1];
                }
                else{
                    $formatedDate .= $date[1];
                }
            }
            if($index1 == false){
                //date here
                if(strlen($date[0]) < 2){
                    $formatedDate .= '/0' . $date[0];
                }
                else{
                    $formatedDate .= '/' . $date[0];
                }
            }
            else if($index2 == false){
                //date here
                if(strlen($date[1]) < 2){
                    $formatedDate .= '/0' . $date[1];
                }
                else{
                    $formatedDate .= '/' . $date[1];
                }
            }
        }
        $yearGot = '';
        if(isset($date[2])){
            if(strlen($date[2]) < 4){
                $year = substr($extractYear, 0, 2);
                $yearGot = $year . $date[2];
            }
            else{
                $yearGot = $date[2];
            }
            \Log::info('if');
        }
        else{
            \Log::info('else');
            $yearGot = $extractYear;
        }
        $formatedDate .= '/'.$yearGot;
        return $formatedDate;
    }

    public function testCredit($var){
        $var = str_replace("$", "", $var);
        $var = str_replace(",", "", $var);
        $var = str_replace(":", "", $var);
        $var = str_replace(" ", "", $var);
        $temp = trim($var);
        $tempReturn;
        if(is_numeric($temp) == true){

            $tempReturn =  number_format((float)$temp, 2, '.', '');
        }
        else{
            \Log::debug("we got : ".$temp);
            $temp = explode(' ',$temp);
            \Log::debug("testing credit : ");
            \Log::debug($temp);

            if(isset($temp[count($temp) - 1])){
                if(is_numeric($temp[count($temp) - 1])) {
                    $val = $temp[count($temp) - 1]+0;
                    if(is_float($val)){
                        \Log::debug("value get : ".$temp[count($temp) - 1]);
                        $tempReturn = number_format((float)$temp[count($temp) - 1], 2, '.', '');
                    }
                    else{
                        $tempReturn = "";
                    }
                }
                else{
                    $tempReturn = "";
                }
            }
            else{
                $tempReturn = '';
            }

        }
        return $tempReturn;
    }
}
