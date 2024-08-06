<?php

namespace App\Services;

use App\CustomFacades\CustomClass;
use App\Events\NewMessage;
use App\Jobs\HandleMessageRead;
use App\Jobs\HandlePaymentNotice;
use App\Models\Message;
use App\Models\Order;
use App\Presenters\AuctioneerOrderActionPresenter;
use App\Presenters\OrderStatusPresenter;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;


class OrderService extends OrderRepository
{
    public function createOrder($lot)
    {
        $now = Carbon::now();


        $commission = $this->getCommission($lot);
        $premium = $this->getPremium($lot);

        $input = [
            'lot_id' => $lot->id,
            'user_id' => $lot->winner_id,
            'status' => 0,
            'payment_due_at' => $now->addDays(7),
            'subtotal' => $lot->current_bid,
            'owner_real_take' => $lot->current_bid - $commission,
            'commission' => $commission,
            'premium' => $premium,
            'earning' => $premium + $commission,
        ];

        $order = OrderRepository::create($input);

        OrderRepository::createOrderRecord(0, $order->id);

        if(config('app.env') == 'production') {
            HandlePaymentNotice::dispatch($order, 0)->delay(Carbon::now()->addDays(3));
        } else {
            HandlePaymentNotice::dispatch($order, 0)->delay(Carbon::now()->addSeconds(90));
        }


        return $order;
    }


    public function getOrder($orderId)
    {
        return OrderRepository::find($orderId);
    }

    public function confirmOrder($request, $orderId)
    {
        $input = [
            'payment_method'=>$request->payment_method,
            'delivery_method'=>$request->delivery_method,
            'delivery_cost'=>$request->delivery_cost,
            'subtotal'=>$request->subtotal,
            'total'=>$request->total,
        ];


        $this->storeOrderLogisticInfo($request, $orderId);


        OrderRepository::update($input, $orderId);
        OrderRepository::updateOrderStatus(10, $orderId);
    }

    public function storeOrderLogisticInfo($request, $orderId)
    {
        $input = [
            'type'=>0,
            'addressee_name'=>$request->recipient_name,
            'addressee_phone'=>$request->recipient_phone,
            'remark'=>$request->remark
        ];
        switch ($request->delivery_method) {
            case 1:
                $input = array_merge($input, [
                    'delivery_zip_code' => $request->delivery_zip_code,
                    'delivery_address' => $request->delivery_address,
                ]);
                break;
            case 2:
                $input = array_merge($input, [
                    'cross_board_delivery_country'=> $request->cross_board_delivery_country,
                    'cross_board_delivery_country_code' => $request->cross_board_delivery_country_code,
                    'cross_board_delivery_address' => $request->cross_board_delivery_address
                ]);
                break;
        }

        OrderRepository::createLogisticRecord($input, $orderId);
    }

    public function getLogisticInfo($order, $type)
    {
        return $order->logisticRecords->where('type', $type)->first();
    }

    public function hasPaid($request, $orderId, $status=null)
    {
        $order = $this->getOrder($orderId);

        if($status == null) {
            if($order->delivery_method === 0) {
                $status = 12;
            } else {
                $status = 13;
            }
        }

        $input = [
            'payment_method' => $order->payment_method,
            'system_order_id' => $request->OrderID,
            'av_code' => $request->AvCode,
            'amount' => $order->total
        ];

        OrderRepository::updateOrderStatusWithTransaction($input, $status, $orderId);
    }

    public function ajaxGetOrders()
    {
        $orders = OrderRepository::all()->sortByDesc('created_at');
        $orderStatusPresenter = new OrderStatusPresenter;
        $orderActionPresenter = new AuctioneerOrderActionPresenter;
        $datatable = DataTables::of($orders)
            ->addColumn('id', function ($order) {
                return $order->id;
            })
            ->addColumn('name', function ($order) {
                return '<a href="'.route('auctioneer.orders.show', $order).'">'.$order->lot->name.'</a>';
            })
            ->addColumn('order_status', function ($order) use ($orderStatusPresenter) {
                if($order->process_status === 6 && $order->owner_take_status === 1) {
                    return '已匯款給賣家';
                } else {
                    return $orderStatusPresenter->present($order);

                }
            })
            ->addColumn('action', function ($order) use ($orderActionPresenter) {
                #return '<div id="ex1" class="modal"><p>Thanks for clicking. That felt good.</p><a href="#" rel="modal:close">Close</a></div><a href="#ex1" rel="modal:open" class="uk-button custom-button-1">Open Modal</a>';
                return '<div class="uk-align-right">'.$orderActionPresenter->present($order).'</div>';
            })
            ->rawColumns(['name', 'order_status', 'action'])
            ->toJson();

        return $datatable;
    }

    public function noticeShipping($orderId)
    {
        $order = OrderRepository::updateOrderStatus(20, $orderId);
    }

    public function storeShippingLogistic($request, $orderId)
    {
        $order = $this->getOrder($orderId);
        $logistic = $this->getLogisticInfo($order, 0);
        $logistic->update([
            'company_name'=>$request->logistics_name,
            'tracking_code'=>$request->tracking_code
        ]);
    }

    public function noticeArrival($orderId)
    {
        $order = OrderRepository::updateOrderStatus(21, $orderId);
    }

    public function completeOrder($orderId)
    {
        $order = OrderRepository::updateOrderStatus(40, $orderId);
        $order->lot->update(['status'=>40]);
        CustomClass::sendTemplateNotice(1, 3, 3, $order->id);
    }

    public function noticeRemit($request, $orderId, $type)
    {
        $order = $this->getOrder($orderId);
        if($type == 0) { // 得標者通知已ATM付款
            if($order->status == 10) {
                $status = 11; // 等待確認匯款
            } else {
                $status = 52; // 爭議 - 等待確認匯款
            }
            $input = [
                'status' => $status,
                'payment_method' => 1,
                'amount'=>$order->total,
                'remitter_id'=>Auth::user()->id,
                'remitter_account'=>$request->account_last_five_number,
                'payee_id'=>1
            ];

        } else { #type 1
            $status = 41;
            $owner = $order->lot->owner;
            $input = [
                'status' => $status,
                'payment_method' => 1,
                'amount'=>$order->owner_real_take,
                'remitter_id'=>Auth::user()->id,
                'remitter_account'=>Auth::user()->bank_account_number,
                'payee_id'=>$owner->id,
                'payee_account'=>$owner->bank_name.$owner->bank_branch_name.$owner->bank_account_name.$owner->bank_account_number
            ];
            $order->lot()->update(['status'=>41]);
            CustomClass::sendTemplateNotice($order->lot->owner_id, 3, 4, $orderId);
        }
        OrderRepository::updateOrderStatusWithTransaction($input, $status, $orderId);

    }

    public function noticeConfirmAtmPay($orderId)
    {
        $order = $this->getOrder($orderId);
        if($order->delivery_method == 0) {
            $status = 12;
        } else {
            $status = 13;
        }
        $order = OrderRepository::updateOrderStatus($status, $orderId);
        CustomClass::sendTemplateNotice($order->user_id, 3, 2, $order->id, 1);
    }

    public function sendMessage($request, $orderId)
    {
        $input = [
            'user_id'=>Auth::user()->id,
            'message'=>$request->message,
        ];
        $order = $this->getOrder($orderId);

        if(Auth::user()->id === $order->user_id) {#is winner
            if($order->lot->entrust === 0) {
                $input['target_user_id'] = $order->lot->owner_id;
            } else {
                $input['target_user_id'] = 1;
            }
        } else {#auctioneer or owner send to winner
            $input['target_user_id'] = $order->user_id;
        }

        $message = $order->messages()->create($input);
        broadcast(new NewMessage($message, $order))->toOthers();
        HandleMessageRead::dispatch($message)->delay(Carbon::now()->addSeconds(10));
        #NewMessage::dispatch($message, $order);
    }

    public function haveRead($messageId)
    {
        Message::find($messageId)->update(['read_at'=>Carbon::now()]);
    }

    public function setAllmessageRead(Order $order): void
    {
        $order->messages()->where('read_at', null)->where('user_id','!=', Auth::user()->id)->update(['read_at'=>Carbon::now()]);
    }

    public function getCommission($lot)
    {
        /*$promotionStatus = Cache::get('shop.promotion.status');
        if($promotionStatus === true) {
            $commissionRate = Cache::get('promotion.commission_rate');
            if($commissionRate < 1) {
                return $lot->current_bid * $commissionRate;
            } else {
                return $commissionRate;
            }
        } else {
            return $lot->current_bid * $lot->owner->commission_rate;
        }*/
        return round($lot->current_bid * 0.15);
    }

    public function getPremium($lot)
    {
        /*$promotionStatus = Cache::get('promotion.status');
        if($promotionStatus === true) {
            $premiumRate = Cache::get('promotion.premium_rate');
            if($premiumRate < 1) {
                return $lot->current_bid * $premiumRate;
            } else {
                return $premiumRate;
            }
        } else {
            return $lot->current_bid * $lot->winner->premium_rate;
        }*/
        return round($lot->current_bid * 0.1);
    }

    public function updateOrderStatus($status, $order)
    {
        return parent::updateOrderStatus($status, $order->id);
    }
}
