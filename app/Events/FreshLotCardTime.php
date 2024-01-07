<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FreshLotCardTime implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    protected $lotId;
    protected $dueTime;

    public function __construct($lotId, $dueTime)
    {
        $this->lotId = $lotId;
        $this->dueTime = $dueTime;
    }

    public function broadcastWith()
    {
        return [
            'lotId'=>$this->lotId,
            'dueTime'=>$this->dueTime
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('lotCard');
    }

    public $connection = 'redis';

    public $queue = 'default';
}
