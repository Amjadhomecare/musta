<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SeoGroupMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->group === 'seo') {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
