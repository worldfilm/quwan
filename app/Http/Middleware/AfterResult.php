<?php

namespace App\Http\Middleware;

use Closure;

class AfterResult
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if(isset($response->original['status'])){
            if($response->original['status'] == 401){
                $api_token = $request->header('api_token') ? $request->header('api_token') : $request->input('api_token');
                $text = '请求返回401的路径:' . $request->path() . ' IP:' .$request->getClientIp() . ' Token:' .$api_token;
                \Log::notice($text);
            }
        }
        return $response;
    }
}
