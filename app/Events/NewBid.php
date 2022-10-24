<?php

namespace App\Events;

use App\Models\BidRecord;
use App\Models\Lot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBid implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $lotId;
    protected $bid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($lotId, BidRecord $bid)
    {
        $this->lotId = $lotId;
        $this->bid = $bid;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $lot = Lot::find($this->lotId);
        return [
            'bidderId' => $this->bid->bidder_id,
            'bidderAlias' => $this->bid->bidder_alias,
            'created_at' => date($this->bid->created_at),
            'auction_end_at' => date($lot->auction_end_at),
            'bid'=>$this->bid->bid
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('lots.'.$this->lotId);
    }

    public $connection = 'redis';

    public $queue = 'default';
}
