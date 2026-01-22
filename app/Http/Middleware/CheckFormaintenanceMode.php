<?php

namespace App\Http\Middleware;

use Closure;

class CheckForMaintenanceMode
{
    public function handle($request, Closure $next)
    {
        if ($this->isDownForMaintenance() &&
            !in_array($request->ip(), ['10.63.0.125', '10.63.3.121'])
        ) {
            return response('SEDANG MAINTENANCE', 503);
        }

        return $next($request);
    }

    protected function isDownForMaintenance()
    {
        return file_exists(storage_path('framework/down'));
    }
}