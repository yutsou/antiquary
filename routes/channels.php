<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Lot;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});



Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    $lot = $order->lot;
    if($lot->entrust === 0){
        if($user->id === $order->user_id || $user->id === $lot->owner_id) {
            return true;
        } else {
            return false;
        }
    } else {
        if($user->id === $order->user_id || $user->id === 1) {
            return true;
        } else {
            return false;
        }
    }

});

Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
