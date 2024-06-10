<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\History;
use Auth;
use DateTime;
use App\Jobs\PDFToImages;
use App\Traits\GetS3Url;
use Excel;
use App\Exports\XLSXExport;
use App\Jobs\FetchCoordinates;
use Log;
use App\Traits\SignRequest;
use Spatie\PdfToImage\Pdf;
use App\Models\CaseNumber;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\App;

class APIController extends Controller
{
    function countPages($path) {
        $pdftext = file_get_contents($path);
        $num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
        return $num;
        // $image = new \Imagick();
        // $image->pingImage($path);
        // return $image->getNumberImages();
    }

    function signRequest($param, $base, $endpoint){
        $method ='POST';
        $uri = $endpoint;
        $secretKey  = config('app.AWS_SECRET_ACCESS_KEY');
        $access_key = config('app.AWS_ACCESS_KEY_ID');
        $region = config('app.AWS_DEFAULT_REGION');
        $service = 'sagemaker';
        $options = array(); $headers = array();
        $host = $base;


        $alg = 'sha256';

        $date = new \DateTime( 'UTC' );

        $dd = $date->format( 'Ymd\THis\Z' );

        $amzdate2 = new \DateTime( 'UTC' );
        $amzdate2 = $amzdate2->format( 'Ymd' );
        $amzdate = $dd;

        $algorithm = 'AWS4-HMAC-SHA256';


        $requestPayload = strtolower($param);
        $hashedPayloads = hash("sha256",$requestPayload);
        $hashedPayloads1 = strtolower(hash("sha256",$param));

        $canonical_uri = $uri;
        $canonical_querystring = '';

        $canonical_headers = "content-type:"."application/json"."\n"."host:".$host."\n"."x-amz-content-sha256:".$hashedPayloads1 ."\n"."x-amz-date:".$amzdate."\n";
        $signed_headers = 'content-type;host;x-amz-content-sha256;x-amz-date';
        $canonical_request = "".$method."\n".$canonical_uri."\n".$canonical_querystring."\n".$canonical_headers."\n".$signed_headers."\n".$hashedPayloads1;


        $credential_scope = $amzdate2 . '/' . $region . '/' . $service . '/' . 'aws4_request';
        $string_to_sign  = "".$algorithm."\n".$amzdate ."\n".$credential_scope."\n".hash('sha256', $canonical_request)."";

        $kSecret = 'AWS4' . $secretKey;
        $kDate = hash_hmac( $alg, $amzdate2, $kSecret, true );
        $kRegion = hash_hmac( $alg, $region, $kDate, true );
        $kService = hash_hmac( $alg, $service, $kRegion, true );
        $kSigning = hash_hmac( $alg, 'aws4_request', $kService, true );
        $signature = hash_hmac( $alg, $string_to_sign, $kSigning );
        $authorization_header = $algorithm . ' ' . 'Credential=' . $access_key . '/' . $credential_scope . ', ' .  'SignedHeaders=' . $signed_headers . ', ' . 'Signature=' . $signature;

        $headers = [
                    'Content-Type'=>'application/json',
                    'X-Amz-Date'=>$amzdate,
                    'X-Amz-Content-Sha256'=>$hashedPayloads1,
                    'Authorization'=>$authorization_header];
        return $headers;
    }

    public function fetchCoordinates($value)
    {
        $curl = curl_init();
        $post = [
            "URL" => $value
        ];
        // dd($post);
        $post = json_encode($post);
        $responseHeader = $this->signRequest($post,'runtime.sagemaker.us-west-2.amazonaws.com','/endpoints/tensorflow-inference-2022-05-25-14-45-47-479/invocations');

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://runtime.sagemaker.us-west-2.amazonaws.com/endpoints/tensorflow-inference-2022-05-25-14-45-47-479/invocations',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_HTTPHEADER => array(
            'X-Amz-Content-Sha256: '.$responseHeader['X-Amz-Content-Sha256'],
            'X-Amz-Date: '.$responseHeader['X-Amz-Date'],
            'Authorization: '.$responseHeader['Authorization'],
            'Content-Type: '.$responseHeader['Content-Type'],
            ),
        ));

        $response = json_decode(curl_exec($curl),true);
        curl_close($curl);
        return $response;
    }

    public function fetchCsv($value)
    {
        $curl = curl_init();
        $post = [
            "URL" => $value
        ];
        // dd($post);
        $post = json_encode($post);
        $responseHeader = $this->signRequest($post,'9bcftz1qq2.execute-api.us-west-2.amazonaws.com','/prod/tbl_csv');

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://9bcftz1qq2.execute-api.us-west-2.amazonaws.com/prod/tbl_csv',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_HTTPHEADER => array(
            'X-Amz-Content-Sha256: '.$responseHeader['X-Amz-Content-Sha256'],
            'X-Amz-Date: '.$responseHeader['X-Amz-Date'],
            'Authorization: '.$responseHeader['Authorization'],
            'Content-Type: '.$responseHeader['Content-Type'],
            ),
        ));

        $response = json_decode(curl_exec($curl),true);
        curl_close($curl);
        return $response;
    }

    public function fetchBankInfo($value)
    {
        $curl = curl_init();
        $post = [
            "URL" => $value
        ];
        // dd($post);
        $post = json_encode($post);
        $responseHeader = $this->signRequest($post,'vpbrowauqa.execute-api.us-west-2.amazonaws.com','/prod/query_answer');
        // dd($responseHeader);
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://vpbrowauqa.execute-api.us-west-2.amazonaws.com/prod/query_answer',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_HTTPHEADER => array(
            'X-Amz-Content-Sha256: '.$responseHeader['X-Amz-Content-Sha256'],
            'X-Amz-Date: '.$responseHeader['X-Amz-Date'],
            'Authorization: '.$responseHeader['Authorization'],
            'Content-Type: '.$responseHeader['Content-Type'],
            ),
        ));

        $response = json_decode(curl_exec($curl),true);
        curl_close($curl);
        $data = [
            'statusCode' => $response['statusCode'],
            'body' => json_decode($response['body'],true),
        ];
        return $data;
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

    public function writeCsvFile($data, $fileName, $bankInfo){
        $fileName = $fileName.'.xlsx';
        $filePath = public_path().'/assets/output/'.$fileName;
        if(file_exists($filePath)){
            unlink($filePath);
        }
        $file = fopen($filePath, 'w');
        $extractYear = '';
        if($bankInfo != null){
            if(!empty($bankInfo['Bank_ST_ENDING_DATE'])){
                $bankInfoDate = str_replace("-", "/", $bankInfo['Bank_ST_ENDING_DATE']);
                $bankInfoDate = str_replace(",", "/", $bankInfoDate);
                $bankInfoDate = str_replace(" ", "/", $bankInfoDate);
                $extractYear = substr($bankInfoDate, strrpos($bankInfoDate, '/') + 1);
                if(strlen($extractYear) < 3){
                    $extractYear .= 20 . $extractYear;
                }
            }
            else{
                $bankInfoDate = str_replace("-", "/", $bankInfo['Bank_ST_DATE']);
                $bankInfoDate = str_replace(",", "/", $bankInfoDate);
                $bankInfoDate = str_replace(" ", "/", $bankInfoDate);
                $extractYear = substr($bankInfoDate, strrpos($bankInfoDate, '/') + 1);
                if(strlen($extractYear) < 3){
                    $extractYear .= 20 . $extractYear;
                }
            }
        }
        \Log::info($extractYear);
        \Log::info($bankInfo);
        // exit();

        $bodyArray = [];
        $headerArray = [];
        $dateIndex = '';
        $sizeOfHeader = 0;
        foreach ($data as $key => $value) {
            if($key == 0){

                $tempArray = [];
                $header = explode(',',$value);
                $sizeOfHeader = sizeOf($header);
                if($bankInfo != null){
                    $tempArray[] = 'Owner Name';
                    $tempArray[] = 'Owner Address';
                    $tempArray[] = 'Account No';
                }

                foreach ($header as $key123 => $value123) {
                    if(strtolower(trim($value123)) == 'date'){
                        $dateIndex = $key123;
                    }

                    $tempArray[] = $value123;
                }
                $headerArray[] = $tempArray;
            }
            else{
                $tempArray = [];
                $body = explode(',',$value);
                $sizeOfBody = sizeOf($body);

                $date= trim($body[$dateIndex]);
                $date = str_replace("-", "/", $date);
                $formatedDate = '';
                $date = explode('/',$date);

                if(strlen($date[0]) < 2){
                    $formatedDate .= 0 . $date[0];
                }
                else{
                    $formatedDate .= $date[0];
                }

                if(isset($date[1])){
                    if(strlen($date[1]) < 2){
                        $formatedDate .= '/0' . $date[1];
                    }
                    else{
                        $formatedDate .= '/' . $date[1];
                    }
                }

                if(isset($date[2])){
                    $formatedDate .= '/' . $date[2];
                }
                else{
                    $formatedDate .= '/' . $extractYear;
                }
                $format = 'm/d/Y';
                $d = DateTime::createFromFormat($format, $formatedDate);

                \Log::info($formatedDate);
                \Log::info('fsjafhdasjlkhfsjalkfhsajdlkhfsldkhfjsadhfsahjfshalfksakkjsdlk');

                if($d && $d->format($format) === $formatedDate){
                    \Log::info('matched');
                    if($bankInfo != null){
                        $tempArray[] = $bankInfo['Bank_ST_ACT_OWNER_NAME'];
                        $tempArray[] = $bankInfo['Bank_ST_ACT_OWNER_ADDR'];
                        $tempArray[] = $bankInfo['Bank_ST_ACT_NMBR'];
                    }
                    foreach ($body as $key123 => $value123) {
                        $result = str_replace(":", "", $value123);
                        $result = str_replace("$", "", $result);
                        $result = $this->testCode($result);

                        if((int)$key123 == (int)$dateIndex){
                            $result = $formatedDate;
                        }
                        $tempArray[] = $result;
                    }
                    $bodyArray[] = $tempArray;
                }



            }
        }
        foreach ($headerArray as $row)
        {
            fputcsv($file, $row);
        }
        // fputcsv($file, $headerArray);
        // dd($headerArray,$bodyArray);
        foreach ($bodyArray as $row)
        {
            fputcsv($file, $row);
        }
        fclose($file);
        $data =  [
                '0' => $fileName,
                '1' => asset('assets/output/'.$fileName),
                'data' => $bodyArray
        ];
        return $data;
    }

    public function convert_pdf_to_images_api(Request $request){
        try{
            $data =  $request->all();
            Log::Debug($data);
            $filename = $data['filename'];
            $post = $data['file'];
            $pageSize = $data['pageSize'];
            $recordId = $data['recordId'];
            $user_id = $data['user_id'];
            Log::Debug($filename);
            Log::Debug($pageSize);
            Log::Debug($recordId);
            Log::Debug($user_id);
            Log::Debug($post);
            $pagesInFile = [];
            $orignalPDFFileName = $filename;
            $fileNameOrg = $filename.'.jpg';
            $filepath = public_path('assets/uploads/'.$fileNameOrg);
            Log::Debug($filepath);
            $imgExt = new \Imagick();
            $imgExt->setResolution(200, 200);

            $imgExt->readImage($post);

            $imgExt->writeImages($filepath, true);
            Log::Debug("reach here");
            for($i = 0; $i <  $pageSize; $i++){

                if($pageSize < 2){
                    $fileNameOrg = $filename . '.jpg';
                }
                else{
                    $fileNameOrg = $filename . '-' . $i . '.jpg';
                }

                $fileName = 'orignal/'.$fileNameOrg;
                \Log::info($fileNameOrg);
                $storage_file = \Storage::disk('public_uploads')->get($fileNameOrg);

                $s3 = \Storage::disk('s3')->put($fileName, $storage_file);

                $url = GetS3Url::GetURLS3($fileName);

                $pagesInFile[$fileNameOrg] = $url;
                \Log::Debug($url);
                if($i == 0){
                    $responseInfo = $this->fetchBankInfo($url);
                    $bankInfo = $responseInfo;
                    if(isset($responseInfo['statusCode']) && $responseInfo['statusCode'] == 200)
                    {
                        $responseInfo = $responseInfo['body'];
                        $bankInfo = $responseInfo;
                    }
                    else{
                        $bankInfo = null;
                    }

                }

            }
            \Log::Debug($pagesInFile);
            $dataToJob = [
                'pagesUrl' => $pagesInFile,
                'filename' => $filename,
                'bankInfo' => $bankInfo,
                'recordId' => $recordId,
                'user_id' => $user_id,
            ];
            return response()->json(['success' => true, 'data' => $dataToJob]);
        }catch(\Exception $e){
            \Log::Debug('job failed');
            \Log::Debug($e->getMessage());
            \Log::Debug($e->getLine());
            \Log::Debug($e);
            $dataToJob = [
                'message' => $e->getMessage(),
                'line_no' => $e->getLine(),
                'exception' => $e,
            ];
            return response()->json(['success' => false, 'data' => $dataToJob]);
        }
    }

    public function convert_pdf_to_csv(Request $request){
        try{
            $pathToPdf = $request->file;
            if($request->has('file')){
                $pageSize = $this->countPages($pathToPdf);
                $file = $pathToPdf->getClientOriginalName();
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $history = [
                    'user_id' => Auth::id(),
                    'input_name' => $filename . '.pdf',
                    'output_name' => $filename . '.xlsx',
                    'user_name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                    'date' => date('Y-m-d H:i:s'),
                    'input_url' => $filename . '.pdf',
                    'output_url' => $filename . '.xlsx',
                    'page_size' => $pageSize,
                    'status' => 0,
                ];
                $recordId = History::insertGetId($history);
                $newName = $filename."-".$recordId;
                $orignalFile = $newName . '.pdf';
                $pathToFile = 'input/' . $orignalFile;
                \Storage::disk('public_uploads_input')->putFileAs('', $pathToPdf, $orignalFile);
                $storage_file = \Storage::disk('public_uploads_input')->get($orignalFile);
                $s3 = \Storage::disk('s3')->put($pathToFile, $storage_file);
                // $url = GetS3Url::GetURLS3($pathToFile);
                unlink(public_path().'/assets/input/'.$orignalFile);

                $fileNameUpdate = [
                    'input_file_id' => $newName . '.pdf',
                    'output_file_id' => $newName . '.xlsx',
                ];
                History::where('id',$recordId)->update($fileNameUpdate);
                return response()->json(['success' => true,'data' => $filename, 'recordId' => $recordId, 'page_size' => $pageSize]);
            }
            else{
                return response()->json(['success' => false, 'message' => 'File does not exists!']);
            }
        }
        catch(\Exception $e){
            return response()->json(['success' => false, 'message' => 'Something went wrong please try again!', 'line'=>$e->getLine(), 'exception' => $e->getMessage()]);
        }
    }

    public function UpdateFile(Request $request){
        try{
            $data = $request->all();

            $update = [
                'output_name' =>  $data['fileName'] . '.xlsx',
                'output_url' =>  $data['fileName'] . '.xlsx',
                'case_number' =>  $data['case_number'],
                'notes' =>  $data['description'],
                'status' => 5
            ];
            $id = $data['recordId'];

            if(!empty($data['case_number'])){
                $caseNumbers = CaseNumber::where([['org_name',Auth::user()->org_name],['case_number',$request->case_number]])->count();
                if ($caseNumbers < 1) {
                    $case['user_id'] = Auth::user()->id;
                    $case['org_name'] = Auth::user()->org_name;
                    $case['case_number'] = $data['case_number'];
                    CaseNumber::insert($case);
                }
            }
            History::where('id',$id)->update($update);
            $userId = Auth::id();
            if (App::environment('local')) {
                $job = new PDFToImages($id,$userId,$data['country']);
            }else{
                $job = (new PDFToImages($id,$userId,$data['country']))->onConnection('redis_worker');
            }
            dispatch($job);
            // PDFToImages::dispatch($id,$userId);
            $response = [
                'success' => true,
                'message' => 'Conversion start successfully!'
            ];
            return response()->json($response);
        }catch(\Exception $e){
            $response = [
                'success' => false,
                'message' => 'Something went wrong Please try again later!',
                'exception' => $e->getMessage(),
                'line_no' => $e->getLine()
            ];
            return response()->json($response);
        }

    }

    public function history123(){
        $data = History::where('user_id',Auth::id())->where('status','>',0)->orderBy('created_at','DESC')->paginate(9);//->get();
        foreach($data as $key => $value){
            $pathToInputFile = 'input/' . $value->input_name;
            $pathToOutputFile = 'output/' . $value->output_name;
            $urlInput = GetS3Url::GetURLS3($pathToInputFile);
            $urlOutput = GetS3Url::GetURLS3($pathToOutputFile);
            $value->input_url = $urlInput;
            $value->output_url = $urlOutput;
        }
        $response = [
            'success' => true,
            'message' => 'All records',
            'data' => $data
        ];
        return response()->json($response);
    }

    public function DeleteRecord(Request $request)
    {
        if(History::where('id',$request->id)->delete()){
            $response = [
                'success' => true,
                'message' => "Record deleted successfully!"
            ];
            return response()->json($response);
        }
        else{
            $response = [
                'success' => false,
                'message' => "Something went wrong!"
            ];
            return response()->json($response);
        }
    }

    public function SetFlag(Request $request)
    {
        $find = History::where('id',$request->id)->first();
        if($find){
            if($find->status == 100 || $find->status == 102){
                if($request->type == 1){
                    $flagVal = false;
                }
                else{
                    $flagVal = true;
                }
                $find->update(['flag'=>$flagVal]);
                $response = [
                    'success' => true,
                    'message' => "Flag updated successfully!",
                ];
                return response()->json($response);
            }
            else{
                $response = [
                    'success' => false,
                    'message' => "Can't set flag while file is under processing!",
                ];
                return response()->json($response);
            }
        }
        else{
            $response = [
                'success' => false,
                'message' => "Record not found!",
            ];
            return response()->json($response);
        }
    }

    public function paginate($items, $perPage = 9, $page = null, $options = [])
    {

        $page = $page ?: (\Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof \Illuminate\Support\Collection ? $items : \Illuminate\Support\Collection::make($items);

        return new \Illuminate\Pagination\LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);

    }

    public function history(Request $request)
    {
        try{
            $data = $request->all();
            $orgName = Auth::user()->org_name;
            $record = History::where('user_id',Auth::id())->where('status','>',0)
            ->orWhere('share_with',$orgName)->orWhere('share_with',Auth::id())
            ->orderBy('date',$data['date_type'])->get();
            foreach($record as $key => $value){
                $pathToInputFile = 'input/' . $value->input_file_id;
                $pathToOutputFile = 'output/' . $value->output_file_id;
                $urlInput = GetS3Url::GetURLS3($pathToInputFile);
                $urlOutput = GetS3Url::GetURLS3($pathToOutputFile);
                $value->input_url = $urlInput;
                $value->output_url = $urlOutput;
            }
            if(isset($data['date']) && !empty($data['date'])){
                foreach($record as $key => $value){
                    $date = date('Y-m-d',strtotime($data['date']));
                    $date1 = date('Y-m-d',strtotime($value->date));
                    if($date != $date1){
                        unset($record[$key]);
                    }
                }
            }
            if(isset($data['flag']) && !empty($data['flag'])){
                foreach($record as $key => $value){
                    if($value->flag != $data['flag']){
                        unset($record[$key]);
                    }
                }
            }
            if(isset($data['type']) && !empty($data['type'])){
                foreach($record as $key => $value){
                    $check = false;
                    if(pathinfo($value->input_name, PATHINFO_EXTENSION) == $data['type']){
                        $check = true;
                    }
                    if(pathinfo($value->output_name, PATHINFO_EXTENSION) == $data['type']){
                        $check = true;
                    }
                    if($check == false){
                        unset($record[$key]);
                    }
                }
            }
            if(isset($data['search']) && !empty($data['search'])){
                foreach($record as $key => $value){
                    $check = false;
                    if(str_contains(strtolower($value->input_name), strtolower($data['search'])))
                    {
                        $check = true;
                    }
                    if(str_contains(strtolower($value->output_name), strtolower($data['search'])))
                    {
                        $check = true;
                    }
                    if(str_contains(strtolower($value->user_name), strtolower($data['search'])))
                    {
                        $check = true;
                    }
                    if(str_contains(strtolower($value->case_number), strtolower($data['search'])))
                    {
                        $check = true;
                    }
                    if(str_contains(strtolower($value->notes), strtolower($data['search'])))
                    {
                        $check = true;
                    }
                    if($check == false){
                        unset($record[$key]);
                    }
                }
            }
            $filterRecord = [];
            foreach($record as $key => $value){
                $filterRecord[] = $value;
            }
            $data = $this->paginate($filterRecord);
            $response = [];
            $response["success"] = true;
            $response["data"] = $data;
            return response()->json($response);
        }
        catch(\Exception $e){
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => $e
            ];
            Log::debug($response);
            return response()->json($response);
        }
    }

    public function convert_pdf_to_images(Request $request){
        $pathToPdf = $request->file;
        try{
            if($request->has('file')){
                $pageSize = $this->countPages($pathToPdf);
                $file = $pathToPdf->getClientOriginalName();
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                // $pathToFile = 'input/'.$filename.'.pdf';
                // $s3 = \Storage::disk('s3')->put($pathToFile, $pathToPdf);
                $pagesInFile = [];
                $cropUrls = [];
                $orignalPDFFileName = $filename;

                for($i = 1; $i <= $pageSize; $i++){
                    $fileNameOrg = $filename.'_'.$i.'.jpg';
                    $filepath = public_path('assets/uploads/'.$fileNameOrg);

                    $pdf = new Pdf($pathToPdf);
                    $getFilePage = $pdf->setPage($i)->saveImage($filepath);

                    $fileName = 'orignal/'.$fileNameOrg;
                    $storage_file = \Storage::disk('public_uploads')->get($fileNameOrg);

                    $s3 = \Storage::disk('s3')->put($fileName, $storage_file);
                    $s3Client = \Storage::disk(config('filesystems.s3'))->getDriver()->getAdapter()->getClient();

                    $cmd = $s3Client->getCommand('GetObject', [
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Key'    => $fileName
                    ]);
                    $s3Request = $s3Client->createPresignedRequest($cmd, '+5 days');

                    $url = (string) $s3Request->getUri();
                    $pagesInFile[$fileNameOrg] = $url;
                }
                foreach ($pagesInFile as $key => $value) {
                    $response = $this->fetchCoordinates($value);
                    \Log::info($response);
                    // if(isset())
                    $coordinates = $response[0]['rois'];
                    $checkCropValues = $response[0]['class_ids'];
                    // dd($coordinates,$checkCropValues);

                    foreach($coordinates as $key1 => $value) {
                        if($checkCropValues[$key1] == 1 || $checkCropValues[$key1] == 5 || $checkCropValues[$key1] == 6|| $checkCropValues[$key1] == 7){
                            $image= public_path().'/assets/uploads/'.$key;

                            list( $width,$height ) = getimagesize( $image );
                            $x1 = $value[1];
                            if($value[1] - 15 > 0){
                                $x1 = $value[1] - 15;
                            }
                            elseif($value[1] - 10 > 0){
                                $x1 = $value[1] - 10;
                            }
                            $y1 = $value[0];
                            if($value[0] - 15 > 0){
                                $y1 = $value[0] - 15;
                            }
                            elseif($value[0] - 10 > 0){
                                $y1 = $value[0] - 10;
                            }

                            $x2 = $value[3];
                            $y2 = $value[2];
                            $w = $x2 - $x1;
                            $h = $y2 - $y1;
                            $thumb = imagecreatetruecolor( $width, $height );
                            $source = imagecreatefromjpeg($image);

                            imagecopyresized($thumb, $source, 0, 0, 0, 0, $width, $height, $width, $height);
                            imagejpeg($thumb,$image,100);


                            $im = imagecreatefromjpeg($image);
                            $dest = imagecreatetruecolor($w,$h);

                            imagecopyresampled($dest,$im,0,0,$x1,$y1,$w,$h,$w,$h);
                            $file = public_path('/assets/crops/'.$key.'_'.$key1);
                            imagejpeg($dest,$file, 100);

                            $fileName = 'crop/'.$key.'_'.$key1;
                            $storage_file = \Storage::disk('public_uploads_crop')->get($key.'_'.$key1);

                            $s3 = \Storage::disk('s3')->put($fileName, $storage_file);
                            $s3Client = \Storage::disk(config('filesystems.s3'))->getDriver()->getAdapter()->getClient();

                            $cmd = $s3Client->getCommand('GetObject', [
                                'Bucket' => config('filesystems.disks.s3.bucket'),
                                'Key'    => $fileName
                            ]);
                            $s3Request = $s3Client->createPresignedRequest($cmd, '+5 days');

                            $url = (string) $s3Request->getUri();
                            $cropUrls[] = $url;
                        }

                    }

                }


                return response()->json(['success' => true,'data' => $cropUrls]);
            }
            else{
                return response()->json(['success' => false, 'message' => 'File does not exists!']);
            }
        }
        catch(\Exception $e){
            return response()->json(['success' => false, 'line'=>$e->getLine(), 'message' => 'Something went wrong please try again!', 'exception' => $e->getMessage()]);
        }
    }


    public function ProcessCancel(Request $request){
        History::where('id',$request->id)->delete();
        $response = [
            'success' => true,
            'message' => 'Process terminated successfully!',
        ];
        return response()->json($response);
    }

    public function FileDetail(Request $request){
        $data = History::where('id',$request->id)->first();
        $data->org_name = Auth::user()->org_name;
        $pathToInputFile = 'input/' . $data->input_file_id;
        $pathToOutputFile = 'output/' . $data->output_file_id;
        $data->input_url = GetS3Url::GetURLS3($pathToInputFile);
        $data->output_url = GetS3Url::GetURLS3($pathToOutputFile);
        $data->date = date('Y-m-d',strtotime($data->date));
        $str = $data['time_period'];
	    $arr = explode(" - ",$str);
        if(sizeof($arr) == 2){
            $data->transcation_start = date('Y-m-d',strtotime($arr[0]));
            $data->transcation_end =  date('Y-m-d',strtotime($arr[1]));
        }
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function mergesort($array) {
        $cmp_function = 'strcmp';
        // Arrays of size < 2 require no action.
        if (count($array) < 2) return;
        // Split the array in half
        $halfway = count($array) / 2;
        $array1 = array_slice($array, 0, $halfway);
        $array2 = array_slice($array, $halfway);
        // Recurse to sort the two halves
        $this->mergesort($array1, $cmp_function);
        $this->mergesort($array2, $cmp_function);
        // If all of $array1 is <= all of $array2, just append them.
        if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
            $array = array_merge($array1, $array2);
            return;
        }
        // Merge the two sorted arrays into a single sorted array
        $array = array();
        $ptr1 = $ptr2 = 0;
        while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
            if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
                $array[] = $array1[$ptr1++];
            }
            else {
                $array[] = $array2[$ptr2++];
            }
        }
        // Merge the remainder
        while ($ptr1 < count($array1)) $array[] = $array1[$ptr1++];
        while ($ptr2 < count($array2)) $array[] = $array2[$ptr2++];
        return;
    }
    function getSortOrder($c) {
        // $sortOrder = array("j","c","z","l","a");
        // $pos = array_search($c, $sortOrder);
        // $pos !== false ? $pos : 99999;
        $d=strtotime($c);
        dd(date("m-d-Y", $d));
        return strtotime(date('m-d-Y',$c));
    }
    public function usort(){
        Excel::store(new XLSXExport(2018), 'Wachovia 5.17.22.xlsx','public_uploads_output', \Maatwebsite\Excel\Excel::XLSX);
        return true;
        // $pathToFile = "lp_mask_rcnn_R_101_FPN_3x.yaml";
        // $url = GetS3Url::GetURLS3($pathToFile);
        // return response()->json(['message' => $url]);
        // $dateArray = array("2021-11-11", "2021-10-10","2021-08-10", "2021-09-08");
        $dateArray =
        array (
            0 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT XXXXX40225',
              6 => '0.50',
              7 => '',
              8 => '0.50',
              9 => '',
            ),
            1 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT XXXXX4314B',
              6 => '1.00',
              7 => '',
              8 => '1.50',
              9 => '',
            ),
            2 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 00540225PM',
              6 => '1.10',
              7 => '',
              8 => '2.60',
              9 => '',
            ),
            3 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 540313',
              6 => '1.70',
              7 => '',
              8 => '4.30',
              9 => '',
            ),
            4 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT XXXXX4314B',
              6 => '2.00',
              7 => '',
              8 => '6.30',
              9 => '',
            ),
            5 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 540309',
              6 => '2.50',
              7 => '',
              8 => '8.80',
              9 => '',
            ),
            6 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT XXXXX0313B',
              6 => '2.50',
              7 => '',
              8 => '11.30',
              9 => '',
            ),
            7 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT XXXXX40225',
              6 => '3.50',
              7 => '',
              8 => '14.80',
              9 => '',
            ),
            8 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 540309',
              6 => '3.85',
              7 => '',
              8 => '18.65',
              9 => '',
            ),
            9 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 540314',
              6 => '4.05',
              7 => '',
              8 => '22.70',
              9 => '',
            ),
            10 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 540314',
              6 => '4.90',
              7 => '',
              8 => '27.60',
              9 => '',
            ),
            11 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT MERCHANT BANKCD DEPOSIT XXXXX8862888',
              6 => '6.10',
              7 => '',
              8 => '33.70',
              9 => '',
            ),
            12 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 540309',
              6 => '6.30',
              7 => '',
              8 => '40.00',
              9 => '',
            ),
            13 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 00540301 PM',
              6 => '7.85',
              7 => '',
              8 => '47.85',
              9 => '',
            ),
            14 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT XXXXX40299',
              6 => '8.35',
              7 => '',
              8 => '56.20',
              9 => '',
            ),
            15 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT',
              6 => '11.00',
              7 => '',
              8 => '67.20',
              9 => '',
            ),
            16 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 00540301PM',
              6 => '13.35',
              7 => '',
              8 => '80.55',
              9 => '',
            ),
            17 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 540301CALE',
              6 => '15.25',
              7 => '',
              8 => '95.80',
              9 => '',
            ),
            18 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT AMERICAN EXPRESS SETTLEMENT 540314',
              6 => '16.12',
              7 => '',
              8 => '111.92',
              9 => '',
            ),
            19 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT MERCHANT BANKCD DEPOSIT XXXXX5684887',
              6 => '16.70',
              7 => '',
              8 => '128.62',
              9 => '',
            ),
            20 =>
            array (
              0 => '',
              1 => '0010375480',
              2 => 'CHECKING',
              3 => '',
              4 => '11/01/2021',
              5 => 'ACH DEPOSIT MERCHANT BANKCD DEPOSIT XXXXX8862888',
              6 => '16.85',
              7 => '',
              8 => '145.47',
              9 => '',
            ),
        );

        usort($dateArray, function ($a, $b) {
            if( $this->getSortOrder($a[4]) < $this->getSortOrder($b[4]) ) {
                return -1;
            }elseif( $this->getSortOrder($a[4]) == $this->getSortOrder($b[4]) ) {
                return 0;
            }else {
                return 1;
            }
        });
        echo '<pre>' . print_r($dateArray,true) . '</pre>';
        dd($dateArray);
        // usort($dateArray, function($a, $b) {
        //     dd($a,$b);
        //     if((string)$a[4] == (string)$b[4]){
        //         return 0;
        //     }
        //     return new DateTime($a[4]) <=> new DateTime($b[4]);
        // });
        // array_walk($dateArray, function (&$v, $k){
        //     // dd($v,$k);
        //     $v = array($v, $k);
        // });
        // usort($dateArray, function ($date1, $date2){
        //     // dd($date1,$date2);
        //     // dd($date1[0][4],$date2[0][4]);
        //     if((string)$date1[0][4] == (string)$date2[0][4]){
        //         return 0;
        //     }
        //     return (strtotime($date1[0][4]) < strtotime($date2[0][4]) ? -1 : 1);
        // });

        // array_walk($dateArray, function (&$v, $k)
        // {
        //     $v[4] = $v[4][0];
        // });
        // $dateArray = $this->mergesort($dateArray);

        print_r($dateArray);
    }

    public function execPythonCommand(){
        $process = new Process(['python3', 'checkPython.py']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $data = $process->getOutput();

        dd($data);
    }
}
