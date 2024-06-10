<?php
namespace App\Traits;

use Exception;

trait GetS3Url{
    public static function GetURLS3($fileName){
        $s3Client = \Storage::disk(config('filesystems.s3'))->getDriver()->getAdapter()->getClient();

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $fileName
        ]);
        $s3Request = $s3Client->createPresignedRequest($cmd, '+5 days');

        $url = (string) $s3Request->getUri();

        return $url;
    }
}