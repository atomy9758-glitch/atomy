<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '관리자 권한이 필요합니다.'], 403);
            }
            
            abort(403, '관리자 권한이 필요합니다.');
        }

        return $next($request);
    }
}
