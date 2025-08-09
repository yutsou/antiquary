<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureMemberIsFullValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (Auth::user()->verification_status === 2) {
                return $next($request);
            } else {
                if ($request->expectsJson()) {
                    // AJAX 或 API 請求，回傳 JSON 和 403
                    return response()->json([
                        'success' => false,
                        'message' => '會員需通過Email和手機驗證才能繼續'
                    ], 403);
                } else {
                    // 一般瀏覽器請求，直接 redirect
                    return redirect()->route('account.profile.edit');
                }
            }
        }
}
