<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\GetS3Url;
use App\Models\History;
use App\Jobs\FetchCoordinates;
use App\Jobs\MakeCSVJob;
use App\Jobs\GenerateCSV;
use App\Jobs\FetchTablesUrlJob;
use Log;
use Illuminate\Support\Facades\App;

class PDFToImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $id;
    private $user_id;
    private $country;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $user_id, $country)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->country = $country;
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
                'status' => 10
            ];
            History::where('id',$this->id)->update($history);

            $record = History::where('id',$this->id)->first();
            $filename = pathinfo($record->input_file_id, PATHINFO_FILENAME);
            $pathToInputFile = 'input/' . $record->input_file_id;
            $urlInput = GetS3Url::GetURLS3($pathToInputFile);
            if($record){
                $history = [
                    'status' => 20,
                ];
                History::where('id',$this->id)->update($history);

                $endpoint = "http://50.18.123.186/api/convert_pdf_to_images_python_script";
                $post = array(
                    'file' => $urlInput,
                    'filename' => $filename,
                    'pageSize' => $record->page_size,
                    'recordId' => $record->id,
                    'user_id' => $this->user_id,
                );
                $result = $this->callAPI($endpoint, $post);

                if(isset($result['success']) && $result['success'] == true)
                {
                    $history = [
                        'status' => 35,
                        'page_size' => $result['page_size'],
                    ];
                    History::where('id',$this->id)->update($history);
                    $dataToJob = [
                        'pagesUrl' => $result['pagesUrl'],
                        'filename' => $filename,
                        'bankInfo' => $result['bankInfo'],
                        'recordId' => $record->id,
                        'user_id' => $this->user_id,
                        'country' => $this->country
                    ];
                    
                    if (App::environment('local')) {
                        $job = new FetchTablesUrlJob($dataToJob);
                    }
                    else{
                        $job = (new FetchTablesUrlJob($dataToJob))->onConnection('redis_worker');
                    }
                    dispatch($job);

                    // $endpoint = "http://50.18.123.186/api/crop_images_python_script";
                    // // $result_ = $this->callAPI($endpoint, $post);

                    // if(isset($result_['success']) && $result_['success'] == true)
                    // {
                    //     \Log::debug("response from crop images API : ");
                    //     // \Log::debug($result_['body']);
                    //     $history = [
                    //         'status' => 50,
                    //         'page_size' => $result['page_size'],
                    //     ];
                    //     History::where('id',$this->id)->update($history);
                    //     $dataToJob = [
                    //         'cropUrl' => $result_['pagesUrl'],
                    //         'filename' => $filename,
                    //         'bankInfo' => $result['bankInfo'],
                    //         'recordId' => $record->id,
                    //         'user_id' => $this->user_id,
                    //         'country' => $this->country,
                    //     ];
                    //     if (App::environment('local')) {
                    //         $job = new MakeCSVJob($dataToJob);
                    //     }else{
                    //         $job = (new MakeCSVJob($dataToJob))->onConnection('redis_worker');
                    //     }
                    //     // if (App::environment('local')) {
                    //     //     $job = new GenerateCSV($dataToJob);
                    //     // }else{
                    //     //     $job = (new GenerateCSV($dataToJob))->onConnection('redis_worker');
                    //     // }
                    //     dispatch($job);

                    //     $response = [
                    //         'success' => true,
                    //         'message' => 'Fetch coordinates start successfully!',
                    //         'data' => $dataToJob
                    //     ];
                    //     Log::debug($response);
                    // }
                    // else{
                    //     $response = [
                    //         'success' => false,
                    //         'message' => 'Crop images => Something went wrong!',
                    //         'data' => $result_
                    //     ];
                    //     Log::debug($response);
                    // }
                }
                else{
                    $response = [
                        'success' => false,
                        'message' => 'PDF to image => Something went wrong!',
                        'data' => $result
                    ];
                    Log::debug($response);
                }
            }
            else{
                $response = [
                    'success' => false,
                    'message' => 'Record not found!',
                ];
                Log::debug($response);
            }
            if($result['success'] == false){
                $history = [
                    'status' => 102,
                    'flag' => 0
                ];
                History::where('id',$this->id)->update($history);
            }
        }catch(\Exception $e){
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

    public function callAPI($endpoint,$post){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        Log::debug("payload of get images from pdf api");
        Log::debug($post);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = json_decode(curl_exec($ch),true);
        return $result;
    }
}


