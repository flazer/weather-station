<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class ApiFormat
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

        if (is_object($response) && property_exists($response, 'original')) {
            $original = $response->original;

            if(is_array($original) && array_key_exists('no_format', $original) && $original['no_format'] === true){
                unset($original['no_format']);
                return $original;
            }

            $collection = new Collection();

            if (is_array($original)) {
                $collection->put('status', "OK");
                if (array_key_exists("result", $original)) {
                    if (!$original['result']) {
                        $collection->put('status', "ERROR");
                    }
                }
                if (array_key_exists("error", $original)) {
                    $collection->put('status', "ERROR");
                    $collection->put('data', $original['error']);
                }else{
                    $collection->put('data', $original);
                }
            }

            $collection->put('time', time());
            $collection->put('debug', $request->fullUrl());

            $response->setContent($collection);
        }

        return $response;
    }
}
