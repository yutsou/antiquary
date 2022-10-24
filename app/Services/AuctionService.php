<?php

namespace App\Services;

use App\Jobs\HandleAuctionStart;
use App\Repositories\AuctionRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuctionService extends AuctionRepository
{
    public function createAuction($request)
    {
        $input = [
            'name'=>$request->name,
            'expert_id'=>Auth::user()->id,
            'start_at'=>$request->auction_start_at,
            'expect_end_at'=>$request->auction_end_at
        ];
        $auction = AuctionRepository::create($input);

        return $auction;
    }

    public function getAuction($auctionId)
    {
        return AuctionRepository::find($auctionId);
    }

    public function getAllAuctions()
    {
        return AuctionRepository::all();
    }
}
