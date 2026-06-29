<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan user sudah login DAN memiliki role 'admin'
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request); 
        }

        // Jika user biasa mencoba menembus endpoint admin, tendang dengan 403
        return response()->json([
            'success' => false,
            'message' => 'Forbidden. Admin access required.'
        ], 403);
    }
}