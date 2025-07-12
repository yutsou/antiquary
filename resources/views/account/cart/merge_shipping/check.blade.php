@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.cart.show') }}" class="custom-color-1 custom-link-mute">購物車</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>

            <!-- 錯誤訊息顯示區塊 -->
            @if(session('error'))
                <div class="uk-alert uk-alert-danger" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- 成功訊息顯示區塊 -->
            @if(session('success'))
                <div class="uk-alert uk-alert-success" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <form class="uk-form-stacked" method="POST" action="{{ route('account.cart.merge_shipping.confirm', $mergeRequest->id) }}">
                @csrf
                <input type="hidden" name="delivery_method" value="{{ $deliveryMethod }}">
                <input type="hidden" name="delivery_cost" value="{{ $deliveryCost }}">
                <input type="hidden" name="recipient_name" value="{{ $recipientName }}">
                <input type="hidden" name="recipient_phone" value="{{ $recipientPhone }}">
                <input type="hidden" name="zip_code" value="{{ $zipCode }}">
                <input type="hidden" name="county" value="{{ $county }}">
                <input type="hidden" name="district" value="{{ $district }}">
                <input type="hidden" name="address" value="{{ $address }}">
                <input type="hidden" name="country" value="{{ $country ?? null}}">
                <input type="hidden" name="country_selector_code" value="{{ $countrySelectorCode ?? null}}">
                <input type="hidden" name="cross_board_address" value="{{ $crossBoardAddress ?? null}}">
                <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">
                <div class="uk-margin">
                    <div class="uk-child-width-expand uk-grid-collapse" uk-grid>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                選擇運送方式
                            </div>
                        </div>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                選擇付款方式
                            </div>
                        </div>
                        <div class="uk-first-column">
                            <div class="uk-text-center" style="border-bottom: 2px solid #003a6c;">
                                訂單確認
                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">收件人資訊</h3>
                        <p>姓名：{{ $recipientName }}</p>
                        <p>電話：{{ $recipientPhone }}</p>
                        @if($deliveryMethod == 1)
                            <p>地址：{{ $zipCode }} {{ $county }}{{ $district }}{{ $address }}</p>
                        @elseif($deliveryMethod == 2)
                            <p>國家：{{ $country }}</p>
                            <p>國家代碼：{{ $countrySelectorCode }}</p>
                            <p>地址：{{ $crossBoardAddress }}</p>
                        @endif
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">運送與付款方式</h3>
                        <p>運送方式：{{ $mergeRequest->delivery_method_text }}</p>
                        <p>運費：NT${{ number_format($deliveryCost) }}</p>
                        <p>付款方式：@if($paymentMethod == 0)信用卡付款@elseif($paymentMethod == 1)ATM轉帳@endif</p>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">商品明細</h3>
                        <div class="uk-margin">
                            @foreach($mergeRequest->items as $item)
                                <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                                    <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                                        <img src="{{ $item->lot->blImages->first()->url }}" alt="" uk-cover>
                                    </div>
                                    <div class="uk-width-expand">
                                        <div class="uk-card-body" style="padding: 20px 20px">
                                            <h3 class="uk-card-title" style="margin: 0 0 10px 0">{{ $item->lot->name }}</h3>
                                            <p>數量: {{ $item->quantity }} | 小計: NT${{ number_format($item->lot->reserve_price * $item->quantity) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="uk-margin uk-text-right">
                    <h3>總金額：NT${{ number_format($mergeRequest->items->sum(function($item){ return $item->lot->reserve_price * $item->quantity; }) + $deliveryCost) }}</h3>
                </div>
                <div class="uk-margin uk-align-right">
                    <button class="uk-button custom-button-1" type="submit">確認下單</button>
                </div>
                <input type="hidden" name="delivery_method" value="{{ $deliveryMethod }}">
                <input type="hidden" name="delivery_cost" value="{{ $deliveryCost }}">
            </form>
        </div>
    </div>
@endsection
