<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictSeoAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->group === 'seo') {
           
            $allowedRoutes = [
                'website.index',
                'homepage.update',
                'login',
                'logout',
                'admin.logout.page',
                'admin.logout'


            ];
            if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                abort(403, 'Access denied for SEO group.');
            }
        }

        return $next($request);
    }
}

