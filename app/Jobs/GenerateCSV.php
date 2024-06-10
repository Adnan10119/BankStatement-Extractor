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

class GenerateCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $url;
    private $filename;
    private $bankInfo;
    private $id;
    private $user_id;
    private $finalCsvArray;
    private $finalCsvHeaderArray;
    private $headerArrayIndex;
    private $current_page;
    private $current_table;
    private $headerReCheckApi;
    private $previousMonth;
    private $currentMonth;
    private $currentYear;
    private $isNewYear;
    private $isSummary;
    private $summary;
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
        $this->finalCsvArray = [];
        $this->finalCsvHeaderArray = [];
        $this->headerArrayIndex = [];
        $this->headerReCheckApi = false;
        $this->previousMonth = 0;
        $this->currentYear = 0;
        $this->currentMonth = 0;
        $this->isNewYear = false;
        $this->isSummary = false;
        $this->summary = [];
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
                'status' => 75
            ];
            History::where('id',$this->id)->update($history);
            $fileRecord = History::where('id',$this->id)->first();
            $fileName = $fileRecord->output_file_id;
            foreach ($this->url as $key3 => $value) {
                \Log::info('fetching csv files at page '.$value['page_no']." and table ".$value['table_no']." ");
                $this->fetchCsv($value);
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
            $isSummaryFind = false;
            if(isset($this->summary['BEGINNING_BALANCE']) && !empty($this->summary['BEGINNING_BALANCE'])){
                $isSummaryFind = true;
                $balance = $this->summary['BEGINNING_BALANCE'];
                $balance = str_replace("$", "", $balance);
                $balance = str_replace(",", "", $balance);
                $balance = trim($balance);
                $index = $index + 1;
                $dataScript = [
                    'user_id' => $this->user_id,
                    'index' => $index,
                    'account_name' => '',
                    'account_number' => '',
                    'account_type' => '',
                    'ck' => '',
                    'date' => '',
                    'description' => 'Beginning Balance',
                    'debit' => '',
                    'credit' => '',
                    'value' => '',
                    'balance' => $balance,
                    'statement_balance' => $balance,
                    'difference' => '',
                ];
                MakeXLSX::insert($dataScript);
                // $statementDate = $data[4] . " - ";
            }
            foreach ($this->finalCsvArray as $key => $data)
            {
                $index = $index + 1;
                $dataScript = [
                    'user_id' => $this->user_id,
                    'index' => $index,
                    'account_name' => isset($data[0])?$data[0]:'',
                    'account_number' => isset($data[1])?$data[1]:'',
                    'account_type' => isset($data[2])?$data[2]:'',
                    'ck' => isset($data[3])?$data[3]:'',
                    'date' => isset($data[4])?$data[4]:'',
                    'description' => isset($data[5])?$data[5]:'',
                    'debit' => '',
                    'credit' => '',
                    'value' => '',
                    'balance' => '',
                    'statement_balance' => '',
                    'difference' => '',
                ];
                if(isset($data[6]) && !empty($data[6])){
                    $dataScript['debit'] = floatval($data[6]);
                }
                if(isset($data[7]) && !empty($data[7])){
                    $dataScript['credit'] = floatval($data[7]);
                }
                // if(isset($data[8]) && !empty($data[8])){
                //     $dataScript['value'] = floatval($data[8]);
                // }
                if(isset($data[10]) && !empty($data[10])){
                    $dataScript['balance'] = floatval($data[10]);
                }
                if(isset($data[9]) && !empty($data[9])){
                    $dataScript['statement_balance'] = floatval($data[9]);
                }
                if(isset($data[11]) && !empty($data[11])){
                    $dataScript['difference'] = floatval($data[11]);
                }
                if($key == 0){
                    $balance = 0;
                    $statementDate = $data[4] . " - ";
                    if(isset($data[9]) && !empty($data[9])){
                        $balance = $data[9];
                    }
                    else if(isset($data[8]) && !empty($data[8])){
                        $balance = $data[8];
                    }
                    $dataScript['balance'] = $balance;
                }
                if($key == $sizeOfArray-1){
                    $statementDate .= $data[4];
                    $history = [
                        'time_period' => $statementDate
                    ];
                    History::where('id',$this->id)->update($history);
                }
                if($key == 0 && $isSummaryFind == false){
                    MakeXLSX::insert($dataScript);
                }
                else if($key == 0 &&  $isSummaryFind == true && trim($dataScript['description']) != 'Beginning Balance'){
                    MakeXLSX::insert($dataScript);
                }
                else if($key != 0 && trim($dataScript['description']) != 'Beginning Balance' && trim($dataScript['description']) != 'Ending Balance' && trim($dataScript['description']) != 'BALANCE LAST STATEMENT'){
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

    public function fetchCsv($value)
    {
        try{
            $this->headerReCheckApi = false;
            // https://9bcftz1qq2.execute-api.us-west-2.amazonaws.com/prod/tbl_csv
            $this->current_page = $value['page_no'];
            $this->current_table = $value['table_no'];
            if($value['type'] == 1){
                if($this->isSummary == false){
                    \Log::debug("summary starts here");
                    $this->summary = $this->SummarryApi($value['url']);
                    \Log::debug($this->summary);
                }
            }
            else if((int)$value['type'] == 0 || (int)$value['type'] == 4 || (int)$value['type'] == 5 || (int)$value['type'] == 6)
            {
                $endpoint = "/csv";
                $base = "i7il1w3tml.execute-api.us-west-2.amazonaws.com";
                $Url = "https://i7il1w3tml.execute-api.us-west-2.amazonaws.com/csv";
                $post = [
                    "URL" => $value['url']
                ];
                $response = \App\Traits\ModelAPITrait::index($post, $base, $endpoint, $Url);
                $CsvArray = [];
                if(isset($response['statusCode']) && $response['statusCode'] == 200)
                {
                    \Log::debug($response['body']);
                    $temp = explode("\n", $response['body']);
                    $csvFileArray = [];
                    foreach ($temp as $key => $value1) {
                        if($key == 0){
                            continue;
                        }
                        else if($key == 1){
                            if(trim($value1) != ''){
                                $csvFileArray[] = trim($value1);
                            }
                            else{
                                continue;
                            }
                        }
                        else if(trim($value1) != '') {
                            $csvFileArray[] = trim($value1);
                        }
                    }
                    $CsvArray = [
                        'type' => $value['type'],
                        'page_no' => $value['page_no'],
                        'table_no' => $value['table_no'],
                        'body' => $csvFileArray,
                    ];
                    if(sizeof($csvFileArray) < 1){
                        return;
                    }
                }
                else{
                    $CsvArray = [
                        'type' => $value['type'],
                        'page_no' => $value['page_no'],
                        'table_no' => $value['table_no'],
                        'error' => " Model failed while extracting text at page ".$value['page_no']." and table ".$value['table_no']." ",
                    ];
                    \Log::debug($CsvArray);
                }

                if(isset($data["error"]))
                {
                    $tempArray =  array (
                        0 => '',
                        1 => '',
                        2 => '',
                        3 => '',
                        4 => '',
                        5 => $data["error"],
                        6 => '',
                        7 => '',
                        8 => '',
                        9 => '',
                    );
                    array_push($this->finalCsvArray,$tempArray);
                    \Log::debug($data["error"]);
                    \Log::debug($e->getMessage());
                    \Log::debug(" at line = ".$e->getLine());
                }
                else{
                    $this->formatData($CsvArray,$value['type'],false);
                }
                // \Log::debug("Writing CSV file");
                // \Log::info($this->finalCsvHeaderArray);
                // \Log::info($this->finalCsvArray);
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

    public function formatData($data,$type,$isHeaderExist){
        try{
            $extractYear = $this->bankInfo();
            $this->currentYear = $extractYear;
            \Log::debug("abc def ghi");
            \Log::debug($data);
            \Log::info('single array after formating!');
            $bankInfo = $this->bankInfo;
            $tempArrayHeader = [];
            $tempArrayBody = [];
            $isHeader = false;
            foreach ($data["body"] as $key => $value) {
                if($isHeader == false && $isHeaderExist == false){
                    $tempHeaderArrayIndex = [];
                    $tempArray = [];
                    $header = explode(',',$value);
                    if($bankInfo != null){
                        $tempArray[] = 'Account Name';
                        $tempArray[] = 'Account No';
                        $tempArray[] = 'Account Type';
                        $tempArray[] = 'Ck#';
                    }
                    $countHeader = 0;
                    foreach ($header as $key123 => $value123) {
                        \Log::debug("column = ".strtolower(trim($value123)));
                        if(str_contains(strtolower(trim($value123)), 'date') && !str_contains(strtolower(trim($value123)), 'value date')){
                            $tempHeaderArrayIndex["Date"] = $key123;
                            $tempArray[] = "Date";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'value date')){
                            $tempHeaderArrayIndex["Value Date"] = $key123;
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'credit')){
                            $tempHeaderArrayIndex["Credit"] = $key123;
                            $tempArray[] = "Credit";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'debit')){
                            $tempHeaderArrayIndex["Debit"] = $key123;
                            $tempArray[] = "Debit";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'balanc')){
                            $tempHeaderArrayIndex["Balance"] = $key123;
                            $tempArray[] = "Balance";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'amount')){
                            $tempHeaderArrayIndex["Value"] = $key123;
                            $tempArray[] = "Value";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'description')){
                            $tempHeaderArrayIndex["Description"] = $key123;
                            $tempArray[] = "Description";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'narration') || str_contains(strtolower(trim($value123)), 'naration')){
                            $tempHeaderArrayIndex["Description"] = $key123;
                            $tempArray[] = "Description";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'transactions')){
                            $tempHeaderArrayIndex["Description"] = $key123;
                            $tempArray[] = "Description";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'addition') && in_array($type,[4,5])){
                            $tempHeaderArrayIndex["Value"] = $key123;
                            $tempArray[] = "Value";
                            $countHeader += 1;
                        }
                        else if(str_contains(strtolower(trim($value123)), 'addition')){
                            $tempHeaderArrayIndex["Credit"] = $key123;
                            $tempArray[] = "Credit";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'subtraction') && in_array($type,[4,5])){
                            $tempHeaderArrayIndex["Value"] = $key123;
                            $tempArray[] = "Value";
                            $countHeader += 1;
                        }
                        else if(str_contains(strtolower(trim($value123)), 'subtraction')){
                            $tempHeaderArrayIndex["Debit"] = $key123;
                            $tempArray[] = "Debit";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'check')){
                            $tempHeaderArrayIndex["Ck#"] = $key123;
                            $tempArray[] = "Ck#";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'chq')){
                            $tempHeaderArrayIndex["Ck#"] = $key123;
                            $tempArray[] = "Ck#";
                            $countHeader += 1;
                        }
                        if(str_contains(strtolower(trim($value123)), 'cod')){
                            $tempHeaderArrayIndex["Cod"] = $key123;
                            $tempArray[] = "Cod";
                            $countHeader += 1;
                        }
                    }
                    $headerCheckCount = 0;
                    foreach($tempArray as $headerTempArray){
                        if($headerTempArray == "Date" || $headerTempArray == "Description"){
                            $headerCheckCount += 1;
                        }
                    }
                    if($headerCheckCount == 2){
                        $isHeader = true;
                    }
                    \Log::debug("count : ".$countHeader);
                    \Log::debug("size of header : ". (sizeof($header) - 1));
                    if($countHeader != (sizeof($header) - 1) && $this->headerReCheckApi == false && $isHeader == true){
                        $this->headerReCheckApi = true;

                        $post = [
                            "body" => $data["body"],
                            "type" => $type,
                        ];
                        $endpoint = "/prod";
                        $base = "fpsge31agj.execute-api.us-west-2.amazonaws.com";
                        $Url = "https://fpsge31agj.execute-api.us-west-2.amazonaws.com/prod";

                        $response = \App\Traits\ModelAPITrait::index($post, $base, $endpoint, $Url);
                        $CsvArray = [];
                        \Log::debug("header correct api response");
                        \Log::debug($response);
                        if(isset($response['statusCode']) == 200)
                        {
                            $this->formatData($response,$type,false);
                            return;
                        }
                        else{
                            $error = " System failed while formating at page ".$this->current_page." and table ".$this->current_table." ";
                            $tempArray =  array (
                                0 => $error,
                                1 => '',
                                2 => '',
                                3 => '',
                                4 => '',
                                5 => '',
                                6 => '',
                                7 => '',
                                8 => '',
                                9 => '',
                            );
                            array_push($this->finalCsvArray,$tempArray);
                            \Log::debug($error);
                            return;
                        }
                    }

                    if($isHeader == true){
                        array_push($this->headerArrayIndex,$tempHeaderArrayIndex);
                        $tempArrayHeader = $tempArray;
                    }
                    \Log::debug("size of body = ".sizeof($data["body"]));
                    \Log::debug("key at index = ". ($key + 1));
                    if(sizeof($data["body"]) == $key + 1){
                        $isHeaderExist = true;
                        $tempArrayHeader = $this->finalCsvHeaderArray[0];
                        $tempHeaderArrayIndex = $this->headerArrayIndex[0];
                        $this->formatData($data,$type,$isHeaderExist);
                    }
                }
                else{
                    if($isHeaderExist == true){
                        $tempArrayHeader = $this->finalCsvHeaderArray[0];
                        $tempHeaderArrayIndex = $this->headerArrayIndex[0];
                    }
                    \Log::debug("body start here");
                    $tempArray = [];
                    $body = explode(',',$value);
                    $sizeOfBody = sizeOf($body);
                    // $dateIndex = $this->headerArrayIndex[0]["Date"];
                    $dateIndex = $tempHeaderArrayIndex["Date"];
                    if(!isset($body[$dateIndex]) || empty($body[$dateIndex])){
                        \Log::debug(" body = ");
                        \Log::info($body);
                        \Log::info(" size of body = ".$sizeOfBody);
                        \Log::info(" index of date = ".$dateIndex);
                        \Log::debug("Date index found empty");
                        continue;
                    }
                    \Log::info($body[$dateIndex]);
                    $date = trim($body[$dateIndex]);
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
                    if(isset($date[0]) && isset($date[1])){

                        if($date[0] <= 12 || strlen($date[0]) > 2){
                            $index1 = true;
                        }
                        else if($date[1] <= 12 || strlen($date[1]) > 2){
                            $index2 = true;
                        }

                        if($index1 == true){
                            //month at index 0
                            foreach($monthArray as $month){
                                if(str_contains(strtolower(trim($date[0])), $month)){
                                    $date[0] = $month_Dict[strtolower($date[0])];
                                }
                            }
                            if(strlen($date[0]) < 2){
                                $formatedDate .= '0' . $date[0];
                            }
                            else{
                                $formatedDate .= $date[0];
                            }
                        }
                        else if($index2 == true){
                            //month at index 1
                            foreach($monthArray as $month){
                                if(str_contains(strtolower(trim($date[1])), $month)){
                                    $date[1] = $month_Dict[strtolower($date[1])];
                                }
                            }
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

                        // if(strlen($date[0]) < 2){
                        //     $formatedDate .= 0 . $date[0];
                        // }
                        // else{
                        //     $formatedDate .= $date[0];
                        // }


                        // $this->currentMonth = $date[0];
                        // if((int)$this->previousMonth == 0){
                        //     $this->previousMonth = $date[0];
                        // }
                        // if((int)$this->currentMonth < (int)$this->previousMonth){
                        //     $this->isNewYear = true;
                        // }
                        // $this->previousMonth = $this->currentMonth;
                    }

                    // if(isset($date[1])){
                    //     foreach($monthArray as $month){
                    //         if(str_contains(strtolower(trim($date[1])), $month)){
                    //             $date[1] = $month_Dict[strtolower($date[1])];
                    //         }
                    //     }
                    //     if(strlen($date[1]) < 2){
                    //         $formatedDate .= '/0' . $date[1];
                    //     }
                    //     else{
                    //         $formatedDate .= '/' . $date[1];
                    //     }
                    // }
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
                    // if($this->isNewYear == true){
                    //     $this->isNewYear = false;
                    //     $yearGot = (int)$yearGot + 1;
                    // }
                    $formatedDate .= '/'.$yearGot;
                    $date = str_replace("/", "-", $formatedDate);
                    \Log::debug("date : ".$formatedDate);
                    try{
                        $d = DateTime::createFromFormat('m-d-Y', $date);
                        if($d && $d->format('m-d-Y') === $date){

                        }
                        else{
                            $formatedDate = date('m-d-Y', strtotime($date));
                        }

                        $formatedDate = str_replace("-", "/", $formatedDate);
                        // $date = new DateTime($date);
                        // $formatedDate = $date->format('m/d/Y');
                        // $formatedDate = DateTime::createFromFormat('m-d-Y', $date)->format('m/d/Y');
                        // $formatedDate = Carbon::parse($formatedDate)->format('m/d/Y');
                    }
                    catch(\Exception $e){
                        \Log::debug($e->getMessage());
                        \Log::debug($e->getLine());
                        continue;
                    }
                    \Log::info("formated date : ".$formatedDate);
                    $format = 'm/d/Y';
                    $d = DateTime::createFromFormat($format, $formatedDate);

                    if($d && $d->format($format) === $formatedDate){
                        \Log::info('matched');

                        if($bankInfo != null){
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

                            $tempArray[0] = $bankOwnerName;
                            $tempArray[1] = $bankInfo['Bank_ST_ACT_NMBR'];
                            $tempArray[2] = isset($bankInfo['ACCOUNT_TYPE'])?$bankInfo['ACCOUNT_TYPE']:"";
                            $tempArray[3] = '';
                        }

                        foreach ($body as $key123 => $value123) {
                            $result = str_replace(":", "", $value123);
                            $result = str_replace("$", "", $result);
                            $result = $this->testCode($result);

                            \Log::debug("key header : ".$key123." key value : ".$result);

                            $index = (int)$key123 + 4;

                            if(isset($tempHeaderArrayIndex["Date"])){
                                if((int)$key123 == (int)$tempHeaderArrayIndex["Date"]){
                                    $result = $formatedDate;
                                    $tempArray[$index] = $result;
                                    \Log::debug("date found at : ".$tempHeaderArrayIndex["Date"]);
                                }
                            }
                            if(isset($tempHeaderArrayIndex["Value Date"])){
                                $index = $index - 1;
                            }

                            if(isset($tempHeaderArrayIndex["Ck#"])){
                                if((int)$key123 == (int)$tempHeaderArrayIndex["Ck#"]){
                                    $tempArray[3] = $result;
                                    $tempArray[$index] = $result;
                                    \Log::debug("Ck# found at : ".$tempHeaderArrayIndex["Ck#"]);
                                }
                            }
                            if(isset($tempHeaderArrayIndex["Cod"])){
                                if((int)$key123 == (int)$tempHeaderArrayIndex["Cod"]){
                                    $tempArray[$index] = $result;
                                    \Log::debug("Cod found at : ".$tempHeaderArrayIndex["Cod"]);
                                }
                            }

                            if(isset($tempHeaderArrayIndex["Description"])){
                                if((int)$key123 == (int)$tempHeaderArrayIndex["Description"]){
                                    $tempArray[$index] = $result;
                                    \Log::debug("Description found at : ".$tempHeaderArrayIndex["Description"]);
                                }
                            }

                            if(isset($tempHeaderArrayIndex["Debit"])){
                                if((int)$key123 == (int)$tempHeaderArrayIndex["Debit"]){
                                    $tempArray[$index] = $this->testCredit($result);
                                    \Log::debug("Debit found at : ".$tempHeaderArrayIndex["Debit"]);
                                }
                            }

                            if(isset($tempHeaderArrayIndex["Credit"])){
                                if((int)$key123 == (int)$tempHeaderArrayIndex["Credit"]){
                                    $tempArray[$index] = $this->testCredit($result);
                                    \Log::debug("Credit found at : ".$tempHeaderArrayIndex["Credit"]);
                                }
                            }

                            if(isset($tempHeaderArrayIndex["Value"])){
                                if((int)$key123 == (int)$tempHeaderArrayIndex["Value"]){
                                    if($type == 5){
                                        if($result != 0){
                                            $result = $result . '-';
                                        }
                                        else{
                                            $result = $result;
                                        }
                                    }
                                    else{
                                        $result = $result;
                                    }
                                    $tempArray[$index] = $result;
                                    \Log::debug("Value found at : ".$tempHeaderArrayIndex["Value"]);
                                }
                            }

                            if(isset($tempHeaderArrayIndex["Balance"])){
                                if((int)$key123 == (int)$tempHeaderArrayIndex["Balance"]){
                                    $tempArray[$index] = $result;
                                    \Log::debug("Balance found at : ".$tempHeaderArrayIndex["Balance"]);
                                }
                            }

                        }
                    }
                    else{
                        \Log::info('not matched');
                    }
                    if(!empty($tempArray)){
                        $tempArrayBody[] = $tempArray;
                    }

                }
            }
            if(is_array($tempArrayHeader)){
                \Log::debug("payload of final api");
                \Log::debug($tempArrayHeader);
                \Log::debug($tempArrayBody);
                \Log::debug("payload of final api end");
                $response = $this->RecompileFile($tempArrayHeader,$tempArrayBody);
                \Log::info($response);
                if(!isset($response['statusCode'])){
                    $error = " Model failed while formating at page ".$this->current_page." and table ".$this->current_table." ";
                    $tempArray =  array (
                        0 => $error,
                        1 => '',
                        2 => '',
                        3 => '',
                        4 => '',
                        5 => '',
                        6 => '',
                        7 => '',
                        8 => '',
                        9 => '',
                    );
                    array_push($this->finalCsvArray,$tempArray);
                    \Log::debug($error);
                    return;
                }
                foreach($response['body'] as $body){
                    array_push($this->finalCsvArray,$body);
                }
                array_push($this->finalCsvHeaderArray,$tempArrayHeader);

            }
            else{
                $error = " System failed while formating at page ".$this->current_page." and table ".$this->current_table." ";
                $tempArray =  array (
                    0 => $error,
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => '',
                    6 => '',
                    7 => '',
                    8 => '',
                    9 => '',
                );
                array_push($this->finalCsvArray,$tempArray);
                \Log::debug($error);
                return;
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
        }
        \Log::debug(" Extracted year = ".$extractYear." ");
        return $extractYear;
    }

    public function RecompileFile($header,$body)
    {
        $endpoint = "/check_col";
        $base = "rwo1s20lz4.execute-api.us-west-2.amazonaws.com";
        $Url = "https://rwo1s20lz4.execute-api.us-west-2.amazonaws.com/check_col";
        $post = [
            "table_header" => $header,
            "table_body" => $body,
        ];
        $response = \App\Traits\ModelAPITrait::index($post, $base, $endpoint, $Url);
        return $response;
    }

    public function testCode($var){
        $temp = trim($var);
        $tempReturn;
        if(is_numeric($temp) == true){

            $tempReturn =  number_format((float)$temp, 2, '.', '');
        }
        else{
            $tempReturn = $temp;
        }
        return $tempReturn;
    }

    public function testCredit($var){
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
