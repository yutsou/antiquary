<?php

namespace App\Services;

use App\CustomFacades\CustomClass;
use App\Jobs\HandleAuctionEnd;
use App\Jobs\HandleAuctionStart;
use App\Jobs\HandleBeforeAuctionEnd;
use App\Jobs\HandleBeforeAuctionStart;
use App\Jobs\LineNotice;
use App\Jobs\OrderCreate;
use App\Jobs\SendLine;
use App\Models\AutoBid;
use App\Models\Favorite;
use App\Events\NewBid;
use App\Models\BidRecord;
use App\Models\Lot;
use App\Presenters\ExpertLotIndexPresenter;
use App\Presenters\OrderStatusPresenter;
use App\Repositories\LotRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Presenters\LotStatusPresenter;

class LotService extends LotRepository
{
    public function createLot($request)
    {
        $input['name'] = $request->name;
        $input['owner_id'] = Auth::user()->id;
        $input['description'] = $request->description;

        if(isset($request->checkReversePrice)) {
            $input['reserve_price'] = $request->reserve_price;
        }

        if(Auth::user()->role === 2) {
            if(isset($request->entrust)) {
                $input['entrust'] = 1;
            } else {
                $input['entrust'] = 0;
            }
        } else {
            $input['entrust'] = 1;
        }

        $lot = LotRepository::create($input);

        return $lot->id;
    }

    public function syncCategoryLot($lotId, array $categories)
    {
        LotRepository::find($lotId)->categories()->sync($categories);
    }

    public function syncLotImages($lotId, $imageIds)
    {
        LotRepository::find($lotId)->blImages()->sync($imageIds);
    }

    public function attachLotImages($lotId, $imageIds)
    {
        $lot = $this->getLot($lotId);
        $lot->blImages()->attach($imageIds);
    }

    public function getLot($lotId)
    {
        return LotRepository::find($lotId);
    }

    public function ajaxReviewGetLots($mainCategory)
    {
        $lots = $mainCategory->lots;

        $expertLotIndexPresenter =  app(ExpertLotIndexPresenter::class);

        $datatable = DataTables::of($lots)
            ->addColumn('id', function ($lot)
            {
                return $lot->id;
            })
            ->addColumn('name', function ($lot) use($mainCategory)
            {
                return '<a href="'.route('expert.lots.review', [$mainCategory->id, $lot->id]).'">'.$lot->name.'</a>';
            })
            /*->addColumn('specification', function ($lot)
            {
                $specification = $lot->specifications->pluck('value')->toArray();
                return implode(", ", $specification);
            })*/
            ->addColumn('status', function ($lot)
            {
                $lotStatusPresenter = new LotStatusPresenter;
                $orderStatusPresenter = new OrderStatusPresenter;
                switch ($lot->status) {
                    case 3:
                        return $lotStatusPresenter->present($lot->status).' - '.$this->getLogisticInfo($lot, 0)->company_name.': '.$this->getLogisticInfo($lot, 0)->tracking_code;
                    case 22:
                        return $orderStatusPresenter->present($lot->order);
                    default:
                        return $lotStatusPresenter->present($lot->status);
                }
            })
            ->addColumn('entrust', function ($lot)
            {
                if($lot->entrust === 0) {
                    return '否';
                } else {
                    return '是';
                }
            })
            ->addColumn('action', function ($lot) use($expertLotIndexPresenter, $mainCategory)
            {
                return $expertLotIndexPresenter->present($lot, $mainCategory);
            })
            ->rawColumns(['name', 'status', 'action'])
            ->toJson();

        return  $datatable;
    }

    public function grantLot($lotId)
    {
        $lot = $this->getLot($lotId);
        #$lot->application()->update(['remark'=>null]);
        $ownerRole = $lot->owner->role;
        if ($ownerRole == 3) {
            #審核成功，尚未填寫物流碼
            $status = 2;
            $noticeCode = 1;
        } elseif ($ownerRole == 2) {
            if($lot->entrust == 1) {
                #審核成功，尚未填寫物流碼
                $status = 2;
                $noticeCode = 1;
            } else {
                #審核成功，等待競標
                $status = 11;
                $noticeCode = 2;
            }
        }
        $this->getLot($lotId)->update(['status'=>$status]);
        return [$lot, $noticeCode];
    }

    public function ajaxCreateAuctionGetLots($mainCategory)
    {
        $lots = $mainCategory->lots->whereBetween('status', [10, 19]);

        $datatable = DataTables::of($lots)
            ->addColumn('selection', function ($lot)
            {
                return '<label><input class="uk-checkbox" type="checkbox" name="lots[]" value="'.$lot->id.'"></label>';
            })
            ->addColumn('id', function ($lot)
            {
                return $lot->id;
            })
            ->addColumn('specification', function ($lot)
            {
                $specification = $lot->specifications->pluck('value')->toArray();
                return implode(", ", $specification);
            })
            ->addColumn('status', function ($lot)
            {
                $lotStatusPresenter = new LotStatusPresenter;
                return $lotStatusPresenter->present($lot->status);
            })
            ->rawColumns(['selection', 'id', 'specification', 'status'])
            ->toJson();
        return  $datatable;
    }

    public function ajaxExpertGetAuctions($user)
    {
        $auctions = $user->auctions->where('process', '!=', 2);

        $datatable = DataTables::of($auctions)
            ->addColumn('id', function ($auction)
            {
                return $auction->id;
            })
            ->addColumn('name', function ($auction)
            {
                return $auction->name;
            })
            ->addColumn('status', function ($auction)
            {
                return match ($auction->status) {
                    0 => '拍賣會尚未開始',
                    1 => '拍賣會進行中',
                    2 => '拍賣會已結束',
                };

            })
            ->addColumn('startAt', function ($auction)
            {
                return $auction->start_at_format;
            })
            ->addColumn('expectEndAt', function ($auction)
            {
                return $auction->expect_end_at_format;
            })
            ->rawColumns(['id', 'name', 'status', 'startAt', 'expectEndAt'])
            ->toJson();
        return  $datatable;
    }

    public function getApplicationLots($user)
    {
        return $user->ownLots->where('status','<', 10);
    }

    public function getSellingLots($user)
    {
        return $user->ownLots->whereBetween('status',[10,25]);
    }

    public function getReturnedLots($user)
    {
        return $user->ownLots->whereBetween('status',[30, 36]);
    }

    public function getFinishedLots($user)
    {
        return $user->ownLots->whereIn('status', [40, 41]);
    }

    public function getBiddingLot($user)
    {
        $lotIds =  $user->bidRecords->unique('lot_id')->pluck('lot_id');
        $lots = array();
        foreach($lotIds as $lotId) {
            $lot = $this->getLot($lotId);
            if($lot->status === 21) {
                array_push($lots, $lot);
            }
        }
        return $lots;
    }

    public function updateApplication($lotId, $request)
    {
        $lot = $this->getLot($lotId);
        if($request->subCategoryId !== null) {
            $this->syncCategoryLot($lotId, [$request->mainCategoryId, $request->subCategoryId]);
        }
        $lot->update([
            'estimated_price' => $request->estimatedPrice,
            'suggestion' => $request->suggestion
        ]);
        $lot->status = 1;
        $lot->save();
        return $lot;
    }

    public function updateLotName($lotId, $request)
    {
        $lot = $this->getLot($lotId);
        $lot->name = $request->name;
        $lot->save();
    }

    public function updateLot($request, $lotId)
    {
        $lot = $this->getLot($lotId);
        $lot->update([
            'name'=>$request->name,
            'description'=>$request->description,
            'reserve_price'=>$request->reserve_price,
            'status'=>0
        ]);
    }

    public function storeApplicationLogisticInfo($request, $lotId)
    {
        $input = [
            'type'=>0,
            'company_name'=>$request->logistic_name,
            'tracking_code'=>$request->tracking_code,
        ];

        LotRepository::createLogisticRecord($input, $lotId);

        $lot = $this->getLot($lotId);
        $lot->update(['status'=>3]);
    }

    public function receiveLot($lotId)
    {
        $lot = $this->getLot($lotId);
        $lot->update([
            'status'=>11
        ]);
        return $lot;
    }

    public function setLotAuction($lotIds, $auction, $auctionStartAt, $auctionEndAt)
    {
        $startGap = Carbon::now()->diffInSeconds( $auctionStartAt);
        $endGap = Carbon::now()->diffInSeconds($auctionEndAt);

        foreach ($lotIds as $index => $lotId) {
            $auctionEndAtCarbon = Carbon::create($auctionEndAt);
            $lot = $this->getLot($lotId);
            $lot->update([
                    'status'=>20,
                    'auction_id'=>$auction->id,
                    'auction_start_at'=>$auctionStartAt,
                    'auction_end_at'=>$auctionEndAtCarbon->addMinutes($index*3)
                ]);
            CustomClass::sendTemplateNotice($lot->owner_id, 2, 0, $lot->id, null, null, $auctionStartAt);
            HandleAuctionEnd::dispatch($auction)->delay(Carbon::now()->addSeconds($endGap)->addMinutes($index*3)->addSecond());
        }
        HandleBeforeAuctionStart::dispatch($auction)->delay(Carbon::now()->addSeconds($startGap)->subMinutes(2));###要改10
        HandleAuctionStart::dispatch($auction)->delay(Carbon::now()->addSeconds($startGap)->addSeconds(2));
        HandleBeforeAuctionEnd::dispatch($auction)->delay(Carbon::now()->addSeconds($endGap)->subMinutes(2));###要改10
    }

    public function handleFavorite($user, $lotId)
    {
        if($user->favorites()->where('lot_id', $lotId)->exists()) {
            $user->favorites()->where('lot_id', $lotId)->delete();
            return 'removed';
        } else {
            $this->storeFavorite($user, $lotId);
            return 'added';
        }
    }

    public function storeFavorite($user, $lotId)
    {
        $favorite = new Favorite();
        $favorite->user_id = $user->id;
        $favorite->lot_id = $lotId;
        $user->favorites()->save($favorite);
    }

    public function getLogisticInfo($lot, $type)
    {
        return $lot->logisticRecords->where('type', $type)->first();
    }

    public function takeDownLot($lotId)
    {
        $input = [
            'status'=>32
        ];
        LotRepository::update($input, $lotId);
    }

    public function searchLots($query)
    {
        $results = collect();
        $words = explode(" ", $query);
        foreach($words as $word) {
            $results->push(Lot::search($word)->where('status', 20)->get());
        }
        return $results->flatten()->unique('id');
        #
    }

    #type 0 application 1 returned 2 unsold

    public function unsoldLotLogistic($request, $lotId)
    {
        $lot = $this->getLot($lotId);
        if ($lot->status == 25) {
            $type = 3;
            $status = 35;
        } else {#23 or 24
            $type = 2;
            $status = 30;
        }

        $input = [
            'addressee_name' => $request->addressee_name,
            'addressee_phone' => $request->addressee_phone,
            'delivery_zip_code' => $request->zipcode,
            'delivery_address'=>$request->county.$request->district.$request->address,
            'type'=>$type
        ];
        LotRepository::createLogisticRecord($input, $lotId);

        $input = [
            'status'=>$status,
        ];
        LotRepository::update($input, $lotId);
    }

    public function returnedLotLogistic($request, $lotId)
    {
        $input = [
            'addressee_name' => $request->addressee_name,
            'addressee_phone' => $request->addressee_phone,
            'delivery_zip_code' => $request->zipcode,
            'delivery_address'=>$request->county.$request->district.$request->address,
            'type'=>1
        ];
        LotRepository::createLogisticRecord($input, $lotId);

        $input = [
            'status'=>33,
        ];
        LotRepository::update($input, $lotId);
    }

    public function reBiding($lotId)
    {
        $lot = $this->getLot($lotId);
        if ($lot->status == 25) {
            $status = 13;
        } else {#23 or 24
            $status = 12;
        }

        $input = [
            'status'=>$status,
            'current_bid'=>0,
            'auction_id'=>null,
            'auction_start_at'=>null,
            'auction_end_at'=>null,
        ];
        LotRepository::update($input, $lotId);

        $lot = $this->getLot($lotId);
        $lot->autoBids()->delete();
        $lot->bidRecords()->delete();
    }

    public function returnLot($request, $lotId, $type)#1 下架 2 流標退回
    {
        $input = [
            'company_name' => $request->company_name,
            'tracking_code' => $request->tracking_code,
        ];
        $lot = LotRepository::updateLogisticRecord($input, $lotId, $type);

        $status = $lot->status+1;

        $input = [
            'status'=>$status
        ];
        LotRepository::update($input, $lotId);
    }

    public function updateLotStatus($status, $lot)
    {
        return parent::update(['status'=>$status], $lot->id);
    }

    public function test()
    {
        $lot = $this->getLot(2);
        OrderCreate::dispatch($lot);
    }

}
