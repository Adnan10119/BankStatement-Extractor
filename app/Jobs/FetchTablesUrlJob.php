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
use App\Jobs\MakeCSVJob;
use Illuminate\Support\Facades\App;

class FetchTablesUrlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $url;
    private $filename;
    private $bankInfo;
    private $id;
    private $user_id;
    private $country;
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
        $this->country = $post['country'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $cropUrls = [];
            foreach ($this->url as $key => $value) {
                // $value['name'] = explode('/', $value['name'])[1];
                $endpoint = "/crop_table";
                $base = "gtb0ibwrbc.execute-api.us-west-2.amazonaws.com";
                $Url = "https://gtb0ibwrbc.execute-api.us-west-2.amazonaws.com/crop_table";
                $post = [
                    "name" => $value['name'],
                    "url" => $value['url'],
                    "page_no" => $value['page_no']
                ];
                $result = \App\Traits\ModelAPITrait::index($post, $base, $endpoint, $Url);
                if(isset($result['statusCode']) && $result['statusCode'] == true)
                {
                    $body = $result['body'];
                    foreach($body as $tableUrl){
                        $tempCropUrlsArray = [
                            'page_no' => $tableUrl['page_no'],
                            'table_no' => $tableUrl['table_no'],
                            'type' => $tableUrl['type'],
                            'url' => $tableUrl['url'],
                            'page_url' => $value['url']
                        ];
                        array_push($cropUrls,$tempCropUrlsArray);
                    }
                }
                else{
                    $error = "Model failed to get table url at page ".$value['page_no'];
                    \Log::info("Result from crop API response");
                    \Log::info($error);
                    \Log::info($result);
                }
            }
            \Log::info($cropUrls);

            $dataToJob = [
                'cropUrl' => $cropUrls,
                'filename' => $this->filename,
                'bankInfo' => $this->bankInfo,
                'recordId' => $this->id,
                'user_id' => $this->user_id,
                'country' => $this->country,
            ];
            $history = [
                'status' => 66
            ];
            History::where('id',$this->id)->update($history);
            if (App::environment('local')) {
                $job = new MakeCSVJob($dataToJob);
            }else{
                $job = (new MakeCSVJob($dataToJob))->onConnection('redis_worker');
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
}
