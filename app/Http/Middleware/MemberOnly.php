<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MemberOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '인증이 필요합니다.'], 401);
            }
            
            return redirect()->route('login');
        }

        return $next($request);
    }
}
