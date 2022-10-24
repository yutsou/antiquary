<?php

namespace App\Services;

use App\Events\NewBid;
use App\Jobs\HandleAuctionEnd;
use App\Jobs\LineNotice;
use App\Models\AutoBid;
use App\Models\BidRecord;
use App\Models\Lot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BidService
{
    private function getLot($lotId)
    {
        return Lot::find($lotId);
    }

    public function bidRule($bid)
    {
        if ($bid >= 0 &&  $bid <= 500) {
            return 50;
        } elseif ($bid >= 501 &&  $bid <= 5000) {
            return 250;
        }elseif ($bid >= 5001 &&  $bid <= 10000) {
            return 500;
        } elseif ($bid >= 10001 &&  $bid <= 25000) {
            return 2500;
        } elseif ($bid >= 25001 &&  $bid <= 50000) {
            return 5000;
        } elseif ($bid >= 50001 &&  $bid <= 250000) {
            return 10000;
        } elseif ($bid >= 250001 &&  $bid <= 1000000) {
            return 50000;
        }  elseif ($bid >= 1000001) {
            return 100000;
        }
    }

    public function getLotMaxAutoBid($lotId)
    {
        $lot = $this->getLot($lotId);
        return $lot->autoBids()->orderBy('bid', 'desc')->first();
    }

    public function getNextBids($bid)
    {
        $firstBid = $bid+$this->bidRule($bid);
        $secondBid = $firstBid+$this->bidRule($firstBid);
        $thirdBid = $secondBid+$this->bidRule($secondBid);
        return [$firstBid, $secondBid, $thirdBid];
    }

    protected function generateAlias($auctionId) {
        $rand = str_pad(strval(rand(0,9999)), 4, "0", STR_PAD_LEFT);

        if ($this->checkAliasExists($auctionId, $rand)) {
            return $this->generateAlias($auctionId);
        }

        return $rand;
    }
    protected function checkAliasExists($auctionId, $alias) {
        return DB::table('auction_user')->where('auction_id', $auctionId)->where('alias', $alias)->exists();
    }

    public function bidLot($lotId, $bidderId, $bid)
    {
        $lot = $this->getLot($lotId);

        $first = Carbon::createFromFormat('Y-m-d H:i:s', $lot->auction_end_at);
        $second = Carbon::createFromFormat('Y-m-d H:i:s', $lot->auction_end_at)->subSeconds(150);
        $now = Carbon::now();

        $auction = $lot->auction;

        #when time between last
        if($now->between($second, $first)) {
            $lot->update(['auction_end_at'=>$now->addSeconds(150)]);
            HandleAuctionEnd::dispatch($auction)->delay($now->addSeconds(5));
        }

        /*if($lot->bidRecords->count() != 0) {#notice
            LineNotice::dispatch($lot, $lot->bidRecords()->latest()->first()->bidder_id, $bid, 0);
        }*/

        ######################################

        if ( $result = DB::table('auction_user')->where('auction_id', $auction->id)->where('user_id', $bidderId)->first() ) {
            $alias = $result->alias;
        } else {
            $alias = $this->generateAlias($auction->id);
            $auction->users()->attach($bidderId, ['alias'=>$alias]);
        }


        $lot->update(['current_bid'=>$bid]);
        $bidRecord = new BidRecord();
        $bidRecord->lot_id = $lotId;
        $bidRecord->bidder_id=$bidderId;
        $bidRecord->bidder_alias=$alias;
        $bidRecord->bid = $bid;
        $newBidRecord = $lot->bidRecords()->save($bidRecord);

        NewBid::dispatch($lotId, $newBidRecord);
        #broadcast(new NewBid($lotId, $newBidHistory))->toOthers();
    }

    public function manualBidLot($lotId, $bidderId, $bid)
    {
        $lot = $this->getLot($lotId);
        $maxAutoBid = $this->getLotMaxAutoBid($lotId);

        if($lot->bidRecords->count() != 0) {
            $topBidderId = $lot->bidRecords()->latest()->first()->bidder_id;
        } else {
            $topBidderId = null;
        }

        $this->bidLot($lotId, $bidderId, $bid);

        #自動出價
        if($this->checkCurrentAutoBidIsValid($lot, $maxAutoBid)) {
            if($bid < $maxAutoBid->bid) {
                $nextBid = $bid+$this->bidRule($bid);
                if($nextBid >= $maxAutoBid->bid) {
                    $bid = $maxAutoBid->bid;
                    $this->bidLot($lotId, $maxAutoBid->user_id, $bid);
                    $this->dispatchLineNotice($lot, $maxAutoBid->user_id, null, $bid, 1);
                } else {
                    $bid = $nextBid;
                    $this->bidLot($lotId, $maxAutoBid->user_id, $bid);
                    $this->dispatchLineNotice($lot, $maxAutoBid->user_id, null, $bid, 1);
                }
            } elseif ($maxAutoBid->bid == $bid) {
                $bid = $maxAutoBid->bid;
                $this->bidLot($lotId, $maxAutoBid->user_id, $bid);
                $this->dispatchLineNotice($lot, $bidderId, null, $bid, 0);
                $this->dispatchLineNotice($lot, $maxAutoBid->user_id, null, $bid, 1);
            }
        } else {
            #dd('123');
            $this->dispatchLineNotice($lot, $topBidderId, $bidderId, $bid, 0);
        }
    }

    protected function checkCurrentAutoBidIsValid($lot, $lotMaxAutoBid)
    {
        if($lotMaxAutoBid !== null) {
            if($lotMaxAutoBid->bid >= $lot->current_bid && $lotMaxAutoBid->bid >= $lot->reserve_price){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function autoBidLot($lotId, $bidderId, $inputAutoBid)
    {

        $lot = $this->getLot($lotId);
        if($lot->bidRecords->count() == 0) {#第一個出價者
            if($inputAutoBid > $lot->reserve_price) {#自動出價大於底價
                $bid = 0+$this->bidRule(0);
                $this->bidLot($lotId, $bidderId, $bid);
            } else {
                $bid = $inputAutoBid;
                $this->bidLot($lotId, $bidderId, $bid);
            }
        } else {
            $topBidderId = $lot->bidRecords()->latest()->first()->bidder_id;

            if($inputAutoBid > $lot->reserve_price) {#自動出價大於底價
                if($lot->current_bid >= $lot->reserve_price) {#當現在價格大於等於底價時
                    $lotMaxAutoBid = $this->getLotMaxAutoBid($lotId);
                    if($this->checkCurrentAutoBidIsValid($lot, $lotMaxAutoBid)) {#有其他自動出價，並且有效
                        if($inputAutoBid > $lotMaxAutoBid->bid) {#當自動出價大於已有的自動出價
                            if($inputAutoBid >= $lotMaxAutoBid->bid+$this->bidRule($lotMaxAutoBid->bid)) {#當自動出價大於等於目前最高出價的下一個出價
                                if($bidderId == $lotMaxAutoBid->user_id) {#如果自動出價者是已有的自動出價者
                                    #dd(0);
                                    $bid = false;
                                    $lotMaxAutoBid->update(['bid'=>$inputAutoBid]);
                                }
                                else {
                                    #通知已有的自動出價者出價
                                    #dd(1);
                                    $bid = $lotMaxAutoBid->bid+$this->bidRule($lotMaxAutoBid->bid);
                                    $this->bidLot($lotId, $bidderId, $bid);#目前最高出價的下一個出價####wrong
                                    $this->dispatchLineNotice($lot, $topBidderId, $bidderId, $bid, 0);
                                }

                            } else {#當自動出價小於目前最高出價的下一個出價
                                #自動出價者出價
                                #dd(2);
                                $bid = $inputAutoBid;
                                $this->bidLot($lotId, $bidderId, $bid);#直達最頂
                                $this->dispatchLineNotice($lot, $topBidderId, $bidderId, $bid, 0);
                                #通知已有的動出價者
                            }

                        } else {#當自動出價小於已有的自動出價#########
                            #自動出價者出價
                            #dd(3);
                            $bid = $inputAutoBid;
                            $this->bidLot($lotId, $bidderId, $bid);#直達最頂(自動出價金額)
                            $this->dispatchLineNotice($lot, $bidderId, null, $bid, 1);
                            #已有的自動出價者出價
                            if($inputAutoBid+$this->bidRule($inputAutoBid) >= $lotMaxAutoBid->bid) {#當自動出價的下一個出價大於等於目前自動出價者的最高出價
                                $bid = $lotMaxAutoBid->bid;
                                $this->bidLot($lotId, $lotMaxAutoBid->user_id, $bid);#目前最高自動出價者的出價
                                $this->dispatchLineNotice($lot, $lotMaxAutoBid->user_id, null, $bid, 1);
                                $this->dispatchLineNotice($lot, $bidderId, $topBidderId, $bid, 0);
                            } else {#當自動出價的下一個出價小於目前自動出價者的最高出價
                                $bid = $inputAutoBid+$this->bidRule($inputAutoBid);
                                $this->bidLot($lotId, $lotMaxAutoBid->user_id, $bid);#自動出價的下一個出價
                                $this->dispatchLineNotice($lot, $lotMaxAutoBid->user_id, null, $bid, 1);
                                $this->dispatchLineNotice($lot, $bidderId, $topBidderId, $bid, 0);
                            }
                        }

                    } else {#沒有其他自動出價，或是其他自動出價無效
                        #dd(4);
                        if($bidderId == $topBidderId) {
                            $bid = false;
                            $lotMaxAutoBid->update(['bid'=>$inputAutoBid]);
                        } else {
                            $bid = $lot->current_bid + $this->bidRule($lot->current_bid);
                            $this->bidLot($lotId, $bidderId, $bid);
                            $this->dispatchLineNotice($lot, $topBidderId, $bidderId, $bid, 0);
                        }

                    }
                } else {#當現有價格小於底價時
                    #dd(5);
                    $bid = $lot->reserve_price;
                    $this->bidLot($lotId, $bidderId, $bid);#直達底價
                    $this->dispatchLineNotice($lot, $topBidderId, $bidderId, $bid, 0);
                }
            } else {#自動出價小於等於底價
                #dd(6);
                $bid = $inputAutoBid;
                $this->bidLot($lotId, $bidderId, $bid);#直達最頂
                $this->dispatchLineNotice($lot, $topBidderId, $bidderId, $bid, 0);
            }
        }


        $autoBid = AutoBid::where('lot_id', $lotId)->where('user_id', $bidderId)->first();
        if(isset($autoBid)){
            $autoBid->update(['bid'=>$inputAutoBid]);
        } else {
            $autoBid = new AutoBid();
            $autoBid->lot_id = $lotId;
            $autoBid->user_id=$bidderId;
            $autoBid->bid = $inputAutoBid;
            $autoBid->save();
        }

        return $bid;
    }

    public function getBidderLotAutoBid($userId, $lot)
    {
        $bidderAutoBid = $lot->autoBids->where('user_id', $userId);
        if($bidderAutoBid->isNotEmpty()) {
            return $bidderAutoBid->first()->bid;
        } else {
            return 0;
        }
    }

    public function dispatchLineNotice($lot, $userId, $topBidderId, $bid, $type)
    {
        if($lot->bidRecords->count() != 0) {
            if ($type == 0) {
                if($userId != $topBidderId) {
                    LineNotice::dispatch($lot, $userId, $bid, $type);
                }
            } else {#type == 1
                LineNotice::dispatch($lot, $userId, $bid, $type);
            }
        }
    }
}
