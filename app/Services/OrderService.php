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

        // 創建 order item 記錄
        $order->orderItems()->create([
            'lot_id' => $lot->id,
            'quantity' => 1,
            'price' => $lot->current_bid,
            'subtotal' => $lot->current_bid,
            'status' => 'normal',
        ]);

        OrderRepository::createOrderRecord(0, $order->id);

        return $order;
    }


    public function getOrder($orderId)
    {
        return OrderRepository::find($orderId);
    }

    public function getOrderByLotAndUser($lotId, $userId)
    {
        return Order::where('user_id', $userId)
            ->whereHas('orderItems', function($query) use ($lotId) {
                $query->where('lot_id', $lotId);
            })
            ->first();
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
                    'county' => $request->county,
                    'district' => $request->district,
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

        if($order->payment_method == 2) {
            $merchant_trade_no = $request->transactionId;
        } elseif($order->payment_method == 0) {
            $merchant_trade_no = $request->MerchantTradeNo;
        }

        $input = [
            'payment_method' => $order->payment_method,
            #'merchant_trade_no' => $request->MerchantTradeNo,
            'merchant_trade_no' => $merchant_trade_no,
            'trade_no' => $request->TradeNo,
            'amount' => $order->total
        ];

        OrderRepository::updateOrderStatusWithTransaction($input, $status, $orderId);
    }

    public function failPayment($request, $orderId)
    {
        $order = $this->getOrder($orderId);

        if($order->payment_method == 2) {
            $merchant_trade_no = $request->transactionId;
        } elseif($order->payment_method == 0) {
            $merchant_trade_no = $request->MerchantTradeNo;
        }

        $input = [
            'payment_method' => $order->payment_method,
            #'merchant_trade_no' => $request->MerchantTradeNo,
            'merchant_trade_no' => $merchant_trade_no,
            'trade_no' => $request->TradeNo,
            'amount' => $order->total
        ];

        OrderRepository::updateOrderStatusWithTransaction($input, 14, $orderId);
    }

    public function ajaxGetOrders()
    {
        $orders = OrderRepository::all()->sortByDesc('created_at')->load('orderItems.lot');
        $orderStatusPresenter = new OrderStatusPresenter;
        $orderActionPresenter = new AuctioneerOrderActionPresenter;
        $datatable = DataTables::of($orders)
            ->addColumn('id', function ($order) {
                return $order->id;
            })
            ->addColumn('name', function ($order) {
                // 檢查是否有 order items
                if ($order->orderItems && $order->orderItems->count() > 0) {
                    $itemsHtml = '';
                    foreach ($order->orderItems as $item) {
                        $itemsHtml .= '<div>' . $item->lot->name . ' x' . $item->quantity . '</div>';
                    }
                    return '<a href="'.route('auctioneer.orders.show', $order).'">' . $itemsHtml . '</a>';
                } else {
                    // 如果沒有 order items，顯示預設訊息
                    return '<a href="'.route('auctioneer.orders.show', $order).'">無商品資訊</a>';
                }
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
        // 只針對競標商品才更新 lot status
        if ($order->orderItems->count() > 0) {
            $lot = $order->orderItems->first()->lot;
            if ($lot->type == 0) { // 競標商品
                $lot->update(['status'=>40]);
            }
            // 直賣商品（type==1）不動 lot status
        }
        CustomClass::sendTemplateNotice(1, 3, 3, $order->id);
    }

    public function noticeRemit($request, $orderId, $type)
    {
        $order = $this->getOrder($orderId);
        if($type == 0) { // 得標者通知已ATM付款
            foreach($order->orderItems as $item) {
                if($item->lot->type == 0) { // 競標商品
                    $item->lot->update(['status'=>23]); // 競標成功 - 等待買家完成交易
                } // 直賣商品
            }
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
            CustomClass::sendTemplateNotice(1, 7, 0, $order->id, 1); // notice auctioneer to confirm payment
        } else { #type 1
            $status = 41;
            // 獲取第一個商品的賣家資訊
            foreach($order->orderItems as $item) {
                if($item->lot->type == 0) { // 競標商品
                    $owner = $item->lot->owner;
                    $input = [
                        'status' => $status,
                        'payment_method' => 1,
                        'amount'=>$order->owner_real_take,
                        'remitter_id'=>Auth::user()->id,
                        'remitter_account'=>Auth::user()->bank_account_number,
                        'payee_id'=>$owner->id,
                        'payee_account'=>$owner->bank_name.$owner->bank_branch_name.$owner->bank_account_name.$owner->bank_account_number
                    ];

                    $item->lot->update(['status'=>41]);
                    CustomClass::sendTemplateNotice($item->lot->owner_id, 3, 4, $orderId);
                } // 直賣商品
            }
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
            // 獲取第一個商品的資訊
            $firstItem = $order->orderItems->first();
            if (!$firstItem) {
                throw new \Exception('訂單中沒有商品');
            }
            if($firstItem->lot->entrust === 0) {
                $input['target_user_id'] = $firstItem->lot->owner_id;
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

    public function messagesHaveRead($messages)
    {
        foreach($messages as $message) {
            if($message->read_at === null && $message->user_id !== Auth::user()->id) {
                $message->update(['read_at'=>Carbon::now()]);
            }
        }
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

    public function updateOrderStatus($status, $order, $remark = null)
    {
        return parent::updateOrderStatus($status, $order->id, $remark);
    }

    public function createProductOrder($lot)
    {
        $now = Carbon::now();

        // 檢查並扣減庫存
        $lotService = app(LotService::class);
        $inventoryItems = [
            [
                'lot_id' => $lot->id,
                'quantity' => 1
            ]
        ];

        $inventoryResult = $lotService->checkAndDeductInventory($inventoryItems);

        if (!$inventoryResult['success']) {
            // 如果有庫存不足的商品，拋出異常
            $insufficientItems = $inventoryResult['insufficient_items'] ?? [];
            $errorMessage = '以下商品庫存不足：';
            foreach ($insufficientItems as $item) {
                $errorMessage .= "\n{$item['lot_name']} - 需要 {$item['requested_quantity']} 件，庫存 {$item['available_inventory']} 件";
            }
            throw new \Exception($errorMessage);
        }

        $input = [
            'lot_id' => $lot->id,
            'user_id' => Auth::user()->id,
            'status' => 0,
            'payment_due_at' => $now->addDays(7),
            'subtotal' => $lot->reserve_price,
            'owner_real_take' => $lot->reserve_price,
            'commission' => 0,
            'premium' => 0,
            'earning' => $lot->reserve_price,
        ];

        $order = OrderRepository::create($input);

        // 創建 order item 記錄
        $order->orderItems()->create([
            'lot_id' => $lot->id,
            'quantity' => 1,
            'price' => $lot->reserve_price,
            'subtotal' => $lot->reserve_price,
            'status' => 'normal',
        ]);

        OrderRepository::createOrderRecord(0, $order->id);

        return $order;
    }

    public function createCartOrder($userId, $selectedLotIds, $requestData)
    {
        $now = Carbon::now();
        $user = Auth::user();

        // 取得所有商品
        $cartService = app(CartService::class);
        $selectedLots = $cartService->getSelectedCartItems($userId, $selectedLotIds);

        if ($selectedLots->isEmpty()) {
            throw new \Exception('選中的商品不存在於購物車中');
        }

        // 準備庫存檢查項目
        $inventoryItems = [];
        foreach ($selectedLots as $lot) {
            $inventoryItems[] = [
                'lot_id' => $lot->id,
                'quantity' => $lot->cart_quantity
            ];
        }

        // 檢查並扣減庫存
        $lotService = app(LotService::class);
        $inventoryResult = $lotService->checkAndDeductInventory($inventoryItems);

        if (!$inventoryResult['success']) {
            // 如果有庫存不足的商品，拋出異常
            $insufficientItems = $inventoryResult['insufficient_items'] ?? [];
            $errorMessage = '以下商品庫存不足：';
            foreach ($insufficientItems as $item) {
                $errorMessage .= "\n{$item['lot_name']} - 需要 {$item['requested_quantity']} 件，庫存 {$item['available_inventory']} 件";
            }
            throw new \Exception($errorMessage);
        }

        // 計算總小計（重新計算以確保使用正確的價格）
        $subtotal = 0;
        foreach ($selectedLots as $lot) {
            $price = $lot->type === 0 ? $lot->current_bid : $lot->reserve_price;
            $subtotal += $price * $lot->cart_quantity;
        }
        // 運費直接用 requestData['delivery_cost']
        $deliveryCost = intval($requestData['delivery_cost']);
        $total = $subtotal + $deliveryCost;

        $ownerRealTake = 0;
        foreach ($selectedLots as $lot) {
            if ($lot->type == 0) { // 競標商品
                $ownerRealTake = $ownerRealTake + $lot->current_bid;
            }
        }

        // 建立一筆訂單
        $input = [
            'user_id' => $userId,
            'status' => 10,
            'payment_method' => intval($requestData['payment_method']),
            'delivery_method' => intval($requestData['delivery_method']),
            'payment_due_at' => $now->addDays(7),
            'owner_real_take' => $ownerRealTake,
            'subtotal' => $subtotal,
            'delivery_cost' => $deliveryCost,
            'total' => $total,
            'commission' => 0,
            'premium' => 0,
            'earning' => $total,
            'remark' => $requestData['remark'] ?? null,
            // 你可以選擇 lot_id 設定為 null 或第一個商品
            'lot_id' => $selectedLots->first()->id,
        ];

        $order = OrderRepository::create($input);
        OrderRepository::createOrderRecord(0, $order->id);

        // 建立 order_items
        foreach ($selectedLots as $lot) {
            // 競標商品使用 current_bid 作為價格，一般商品使用 reserve_price
            $price = $lot->type === 0 ? $lot->current_bid : $lot->reserve_price;
            $subtotal = $price * $lot->cart_quantity;

            $order->orderItems()->create([
                'lot_id' => $lot->id,
                'quantity' => $lot->cart_quantity,
                'price' => $price,
                'subtotal' => $subtotal,
                'status' => 'normal',
            ]);
        }

        // 建立物流資訊
        $this->storeCartOrderLogisticInfo($requestData, $order->id);


        return $order;
    }

    private function storeCartOrderLogisticInfo($requestData, $orderId)
    {
        $input = [
            'type' => 0,
            'addressee_name' => $requestData['recipient_name'],
            'addressee_phone' => $requestData['recipient_phone'],
        ];

        // 檢查運送方式
        $deliveryMethod = intval($requestData['delivery_method']);

        if ($deliveryMethod == 1) {
            $input['delivery_zip_code'] = $requestData['zip_code'] ?? null;
            $input['county'] = $requestData['county'] ?? null;
            $input['district'] = $requestData['district'] ?? null;
            $input['delivery_address'] = $requestData['address'] ?? null;
        } elseif ($deliveryMethod == 2) {
            $input['cross_board_delivery_country'] = $requestData['country'] ?? null;
            $input['cross_board_delivery_country_code'] = $requestData['country_selector_code'] ?? null;
            $input['cross_board_delivery_address'] = $requestData['cross_board_address'] ?? null;
        }

        return OrderRepository::createLogisticRecord($input, $orderId);
    }

    public function createMergeShippingOrder($userId, $mergeRequest, $requestData)
    {
        $now = Carbon::now();

        // 注意：庫存已經在創建 merge request 時扣減過了，這裡不需要再次檢查和扣減
        // 因為 merge request 創建時已經預先扣減了庫存，所以這裡直接創建訂單即可

        // 計算總計
        $subtotal = $mergeRequest->items->sum(function($item) {
            return $item->lot->reserve_price * $item->quantity;
        });
        $total = $subtotal + $mergeRequest->new_shipping_fee;

        // 創建訂單
        $input = [
            'lot_id' => $mergeRequest->items->first()->lot_id, // 使用第一個商品作為主要商品
            'user_id' => $userId,
            'status' => 10,
            'payment_method' => intval($requestData['payment_method']),
            'delivery_method' => intval($requestData['delivery_method']),
            'payment_due_at' => $now->addDays(7),
            'subtotal' => $subtotal,
            'delivery_cost' => $mergeRequest->new_shipping_fee,
            'total' => $total,
            'commission' => 0,
            'premium' => 0,
            'earning' => $total,
            'remark' => '合併運費訂單 - 請求ID: ' . $mergeRequest->id,
        ];

        $order = OrderRepository::create($input);
        OrderRepository::createOrderRecord(0, $order->id);

        // 為每個 merge shipping item 創建 order item 記錄
        foreach ($mergeRequest->items as $item) {
            $order->orderItems()->create([
                'lot_id' => $item->lot_id,
                'quantity' => $item->quantity,
                'price' => $item->lot->reserve_price,
                'subtotal' => $item->lot->reserve_price * $item->quantity,
                'status' => 'normal',
            ]);
        }

        // 創建運送資訊 - 從 merge request 的 logistic records 中獲取地址信息
        $this->storeMergeShippingOrderLogisticInfoFromRequest($mergeRequest, $order->id);

        return $order;
    }

    private function storeMergeShippingOrderLogisticInfo($requestData, $orderId)
    {
        $input = [
            'type' => 0, // 主物流資訊 - logistic_records type 定義：0=application(正常流程賣場寄給拍賣會), 1=returned(退還競標物品), 2=unsold(競標失敗退還), 3=未付款退回
            'addressee_name' => $requestData['recipient_name'],
            'addressee_phone' => $requestData['recipient_phone'],
        ];

        $deliveryMethod = intval($requestData['delivery_method']);

        if ($deliveryMethod == 1) {
            $input['delivery_zip_code'] = $requestData['zip_code'] ?? null;
            $input['county'] = $requestData['county'] ?? null;
            $input['district'] = $requestData['district'] ?? null;
            $input['delivery_address'] = $requestData['address'] ?? null;
        } elseif ($deliveryMethod == 2) {
            $input['cross_board_delivery_country'] = $requestData['country'] ?? null;
            $input['cross_board_delivery_country_code'] = $requestData['country_selector_code'] ?? null;
            $input['cross_board_delivery_address'] = $requestData['cross_board_address'] ?? null;
        }

        return OrderRepository::createLogisticRecord($input, $orderId);
    }

    private function storeMergeShippingOrderLogisticInfoFromRequest($mergeRequest, $orderId)
    {
        // 從 merge request 的 logistic records 中獲取地址信息
        $logisticRecord = $mergeRequest->logisticRecords()->where('type', 4)->first();

        if (!$logisticRecord) {
            throw new \Exception('找不到合併運費請求的地址信息');
        }

        $input = [
            'type' => 0, // 主物流資訊
            'addressee_name' => $logisticRecord->addressee_name,
            'addressee_phone' => $logisticRecord->addressee_phone,
        ];

        $deliveryMethod = $mergeRequest->delivery_method;

        if ($deliveryMethod == 1) {
            $input['delivery_zip_code'] = $logisticRecord->delivery_zip_code;
            $input['county'] = $logisticRecord->county;
            $input['district'] = $logisticRecord->district;
            $input['delivery_address'] = $logisticRecord->delivery_address;
        } elseif ($deliveryMethod == 2) {
            $input['cross_board_delivery_country'] = $logisticRecord->cross_board_delivery_country;
            $input['cross_board_delivery_country_code'] = $logisticRecord->cross_board_delivery_country_code;
            $input['cross_board_delivery_address'] = $logisticRecord->cross_board_delivery_address;
        }

        return OrderRepository::createLogisticRecord($input, $orderId);
    }

    public function getOrderCountByStatus($statuses)
    {
        $orders = OrderRepository::all()->whereIn('status', $statuses);

        return  $orders->count();
    }
}
