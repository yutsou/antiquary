<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class  OrderCreate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lot;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lot)
    {
        $this->lot = $lot;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(OrderService::class)->createOrder($this->lot);
        #$orderService = new OrderService;
        #$orderService->createOrder($this->lot);
    }
}
