<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    public function handle($request, Closure $next)
    {    
        $request->start = microtime(true);

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $request->end = microtime(true);

        $this->log($request,$response);
    }

    protected function log($request,$response)
    {
        /*$duration = $request->end - $request->start;
        $url = $request->fullUrl();
        $method = $request->getMethod();
        $ip = $request->getClientIp();
        $params = $request->all();
        //print_r($params);exit();
        $log = "{$ip}: {$method}@{$url} - {$duration}ms \n".
        "Request : {[$request->all()]} \n".
        "Response : {$response->getContent()} \n";*/
       
        $endTime = microtime(true);
        $dataToLog = 'Duration: ' . number_format($endTime - LARAVEL_START, 3) . "\n";
        $dataToLog .= 'IP Address: ' . $request->ip() . "\n";
        $dataToLog .= 'URL: '    . $request->fullUrl() . "\n";
        $dataToLog .= 'Method: ' . $request->method() . "\n";
        $dataToLog .= 'Input: '  . $request->getContent() . "\n";
        $dataToLog .= 'Output: ' . $response->getContent() . "\n";

        $filename = 'api_datalogger_' . date('d-m-y') . '.log';
        \File::append( storage_path('logs' . DIRECTORY_SEPARATOR . $filename), $dataToLog . "\n" . str_repeat("=", 20) . "\n\n");

        //Log::info($dataToLog);
    }
}