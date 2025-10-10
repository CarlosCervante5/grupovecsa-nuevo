<?php

namespace App\Http\Middleware;

use App\Mail\AlertAnomalousRequest;
use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class LogBandwidthUsage
{
    protected $maxRequests = 100; // Número máximo de solicitudes permitidas
    protected $decayMinutes = 1;  // Tiempo de decay en minutos

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $timeFrame = now()->subMinutes($this->decayMinutes);

        $requestSize = $this->getRequestSize($request);

        $response = $next($request);

        $responseSize = strlen($response->getContent());

        // Contar solicitudes de la misma IP en el tiempo definido
        $requestCount = DB::table(env('DB_TABLE_PREFIX', '') . 'request_logs')
            ->where('ip_address', $ip)
            ->where('created_at', '>=', $timeFrame)
            ->count();

        // Registrar la solicitud en la base de datos
        DB::table(env('DB_TABLE_PREFIX', '') . 'request_logs')->insert([
            'ip_address' => $ip,
            'path' => $request->path(),
            'method' => $request->method(),
            'request_size' => $requestSize,
            'response_size' => $responseSize,
            'total_size' => $requestSize + $responseSize,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($requestCount > $this->maxRequests) {
            // Enviar correo de alerta
            // Mail::mailer('logs')->to('jgcl.proyectos@gmail.com')->send(new AlertAnomalousRequest($ip, $request->path(), $request->method()));
            Mail::mailer('logs')->to('lesamaoyabi@gmail.com')->send(new AlertAnomalousRequest($ip, $request->path(), $request->method()));

            throw new ThrottleRequestsException('Too many requests from this IP.');
        }

        return $response;
    }

    private function getRequestSize($request)
    {
        $contentLength = $request->header('Content-Length');

        if ($contentLength) {
            return (int) $contentLength;
        }

        return strlen($request->getContent());
    }
}


