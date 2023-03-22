<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BidTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_lot()
    {
        $response = $this->get(route('mart.lots.show', [
            'lotId' => 1,
        ]));

        $response->assertStatus(200);
    }

    public function test_bid_lot()
    {
        $user = User::find(4);
        $response = $this->actingAs($user)->post('/account/axios/lots/manual_bid', [
            'lotId'=> 1,
            'bidderId'=> 4,
            'bid'=> 1000
        ]);

        $response->assertStatus(200);
    }
}
