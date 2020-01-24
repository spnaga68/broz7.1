<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;

class LogAfterRequest
{

    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {

        // print_r(json_encode($request->all())); exit;
        Log::info('app.requests', ['request' => $request->all(), 'response' => $response]);
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/response.txt',$actual_link, FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/response.txt', json_encode($request->all()), FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/response.txt', '/n', FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/response.txt', $response, FILE_APPEND);
    }

}
