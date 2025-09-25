<?php

namespace App\Http\Middleware;

use App\Models\EventLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogEventosMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            if(Auth::check()){
                $userId =  Auth::id();
                $ip = $request->ip();
                $metodo = $request->method();
                $url = $request->path();

                $operacion = match ($metodo){
                    'POST' => 'Creo',
                    'PUT' => 'Actualizo',
                    'Delete' => 'Elimino',
                    default => 'Consulta'
                };

                EventLog::create([
                    'user_id' => $userId ?? 1,
                    'operacion' => $operacion ?? 1,
                    'endpoint' => $url ?? 1,
                    'direccion_ip' => $ip ?? 1
                ]);



            }
        } catch (\Exception $e) {
            //throw $th;
        }

        return $response;
    }
}
