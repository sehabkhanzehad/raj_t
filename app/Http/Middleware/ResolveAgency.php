<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class ResolveAgency
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $agency = $request->user()->agency;

        if (!$agency || !$agency->canAccess($request->user())) return $this->error('Invalid Agency or Access Denied.', 403);

        Context::addHidden('current_agency', $agency);

        return $next($request);
    }
}
