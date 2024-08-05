<?php

namespace App\Http\Middleware;

use App\Models\Lot;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LotOwnership
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
        $lotId = $request->route('lotId'); // Assuming the route parameter is named 'order'
        $lot = Lot::find($lotId);

        if (!$lot || $lot->owner_id !== Auth::id()) {
            // Order does not exist or the user does not own the order
            //return response()->json(['message' => 'Unauthorized'], 403);
            return redirect()->route('mart.warning.show')->with('title', '警告')->with('message', '錯誤的訪問');
        }

        return $next($request);
    }
}
