<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;

class PapertrailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (app('app')->environment() == 'local') return;

        $monolog   = app(\Illuminate\Log\Logger::class)->getLogger();
        //$syslog    = new \Monolog\Handler\SyslogUdpHandler(config('papertrail.host'), config('papertrail.port'));
        $formatter = new \Monolog\Formatter\LineFormatter('%message%', null, true);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        
        $handler = new StreamHandler("/var/log/httpd/laravel.log");
        $handler->setFormatter($formatter);

        $monolog->pushHandler($handler);

        //$syslog->setFormatter($formatter);
        //$monolog->pushHandler($syslog);

        //$monolog->pushHandler(new LogglyHandler('30720a85-ba85-4096-855a-e6615e340f84/tag/monolog'));
    }
}
