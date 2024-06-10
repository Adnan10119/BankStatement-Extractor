<?php
namespace App\Traits;

use Exception;

trait SignRequest{
    public static function signRequest($param, $base, $endpoint){
        $method ='POST';
        $uri = $endpoint;
        $secretKey  = config('app.AWS_SECRET_ACCESS_KEY');//'tejrNEWXx4NMlSC81rkXohy/d00xjTRjyCfmQ5hr';
        $access_key = config('app.AWS_ACCESS_KEY_ID');//'AKIA4LBKETVWEYHNZK5C';
        $region = config('app.AWS_DEFAULT_REGION');//'us-west-2';
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
}
