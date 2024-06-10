<?php
namespace App\Traits;

use Exception;
use App\Traits\SignRequest;

trait ModelAPITrait{
    public static function index($post, $base, $endpoint, $Url){
        $post = json_encode($post);
        \Log::debug($post);
        $responseHeader = SignRequest::signRequest($post,$base,$endpoint);
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $Url,
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
