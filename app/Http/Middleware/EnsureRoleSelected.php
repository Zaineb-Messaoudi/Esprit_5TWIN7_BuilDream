<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->role_setup_completed) {
            return redirect()->route('role.setup');
        }

        return $next($request);
    }
}
