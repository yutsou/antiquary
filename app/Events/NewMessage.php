<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $message;
    protected $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message,Order $order)
    {
        $this->message = $message;
        $this->order = $order;
    }

    public function broadcastWith()
    {
        return [
            'messageId' => $this->message->id,
            'message' => $this->message->message,
            'createdAt' => $this->message->created_at
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        #return new PrivateChannel('channel-name');
        return new PrivateChannel('orders.'.$this->order->id);
        #return new Channel('orders.'.$this->order->id);
    }

    public $connection = 'redis';

    public $queue = 'default';
}
