<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrderOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $orderId = $request->route('orderId'); // Assuming the route parameter is named 'order'
        $order = Order::find($orderId);

        if (!$order || $order->user_id !== Auth::id()) {
            // Order does not exist or the user does not own the order
            //return response()->json(['message' => 'Unauthorized'], 403);
            return redirect()->route('mart.warning.show')->with('title', '警告')->with('message', '錯誤的訪問');
        }

        return $next($request);
    }
}
