<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\GetS3Url;
use App\Traits\SignRequest;
use App\Models\History;
use App\Jobs\GenerateCSV;
use Illuminate\Support\Facades\App;

class FetchCoordinates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $url;
    private $filename;
    private $bankInfo;
    private $id;
    private $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($post)
    {
        $this->url = $post['pagesUrl'];
        $this->filename = $post['filename'];
        $this->bankInfo = $post['bankInfo'];
        $this->id = $post['recordId'];
        $this->user_id = $post['user_id'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            \Log::debug("urls received = ");
            \Log::debug($this->url);
            \Log::debug("urls end");
            $cropUrls = [];
            foreach ($this->url as $key => $value) {
                $value['name'] = explode('/', $value['name'])[1];
                $response = $this->fetchCoordinates($value['url']);
                \Log::info($response);
                if(!isset($response['rois'])){
                    continue;
                }
                $coordinates = $response['rois'];
                $checkCropValues = $response['class_ids'];
                try{
                    file_put_contents(public_path().'/assets/uploads/'.$value['name'], file_get_contents($value['url']));
                }
                catch(\Exception $e){
                    \Log::debug($e);
                    continue;
                }

                foreach($coordinates as $key1 => $value1) {
                    // if($checkCropValues[$key1] == 0 || $checkCropValues[$key1] == 1 || $checkCropValues[$key1] == 4 || $checkCropValues[$key1] == 5|| $checkCropValues[$key1] == 6){
                        $type = $checkCropValues[$key1];
                        $image= public_path().'/assets/uploads/'.$value['name'];

                        list( $width,$height ) = getimagesize( $image );
                        $x1 = $value1[0];
                        if($value1[0] - 25 > 0){
                            $x1 = $value1[0] - 25;
                        }
                        elseif($value1[0] - 20 > 0){
                            $x1 = $value1[0] - 20;
                        }
                        elseif($value1[0] - 15 > 0){
                            $x1 = $value1[0] - 15;
                        }
                        elseif($value1[0] - 10 > 0){
                            $x1 = $value1[0] - 10;
                        }
                        $y1 = $value1[1];
                        if($this->bankInfo['BANK_NAME'] == "Sovereign Bank"){
                            if($value1[1] - 20 > 0){
                                $y1 = $value1[1] - 20;
                            }
                            elseif($value1[1] - 15 > 0){
                                $y1 = $value1[1] - 15;
                            }
                            elseif($value1[1] - 10 > 0){
                                $y1 = $value1[1] - 10;
                            }
                        }
                        $x2 = $value1[2];
                        $y2 = $value1[3];
                        $w = $x2 - $x1;
                        $h = $y2 - $y1;
                        $thumb = imagecreatetruecolor( $width, $height );
                        $source = imagecreatefromjpeg($image);

                        imagecopyresized($thumb, $source, 0, 0, 0, 0, $width, $height, $width, $height);
                        imagejpeg($thumb,$image,100);


                        $im = imagecreatefromjpeg($image);
                        $dest = imagecreatetruecolor($w,$h);

                        imagecopyresampled($dest,$im,0,0,$x1,$y1,$w,$h,$w,$h);

                        $filenameNew = pathinfo($value['name'])['filename'].'-'.$key1.'.jpg';
                        $file = public_path('/assets/crops/'.$filenameNew);
                        imagejpeg($dest,$file, 100);

                        $fileName = 'crop/'.$filenameNew;
                        $storage_file = \Storage::disk('public_uploads_crop')->get($filenameNew);

                        $s3 = \Storage::disk('s3')->put($fileName, $storage_file);

                        $url = GetS3Url::GetURLS3($fileName);

                        unlink($file);

                        $tempCropUrlsArray = [
                            'page_no' => $key+1,
                            'table_no' => $key1+1,
                            'type' => $type,
                            'url' => $url,
                        ];
                        array_push($cropUrls,$tempCropUrlsArray);
                    // }
                }

                unlink(public_path().'/assets/uploads/'.$value['name']);
            }
            \Log::info($cropUrls);

            $dataToJob = [
                'cropUrl' => $cropUrls,
                'filename' => $this->filename,
                'bankInfo' => $this->bankInfo,
                'recordId' => $this->id,
                'user_id' => $this->user_id,
            ];
            $history = [
                'status' => 66
            ];
            History::where('id',$this->id)->update($history);
            if (App::environment('local')) {
                $job = new GenerateCSV($dataToJob);
            }else{
                $job = (new GenerateCSV($dataToJob))->onConnection('redis_worker');
            }
            dispatch($job);
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

    public function fetchCoordinates($value)
    {
        //https://runtime.sagemaker.us-west-2.amazonaws.com/endpoints/pytorch-inference-2022-08-01-06-39-04-099/invocations
        // https://runtime.sagemaker.us-west-2.amazonaws.com/endpoints/pytorch-inference-2022-09-09-11-40-39-639/invocations
        $curl = curl_init();
        $post = [
            "URL" => $value
        ];
        // dd($post);
        $post = json_encode($post);
        $responseHeader = SignRequest::signRequest($post,'runtime.sagemaker.us-west-2.amazonaws.com','/endpoints/pytorch-inference-2022-09-09-11-40-39-639/invocations');

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://runtime.sagemaker.us-west-2.amazonaws.com/endpoints/pytorch-inference-2022-09-09-11-40-39-639/invocations',
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
}
