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

            <form class="uk-form-stacked" method="POST" action="{{ route('account.cart.check') }}"
                  enctype="multipart/form-data">
                @csrf
                @foreach($selectedLotIds as $lotId)
                    <input type="hidden" name="selected_lots[]" value="{{ $lotId }}">
                @endforeach
                <input type="hidden" name="delivery_method" value="{{ $deliveryMethod }}">
                <input type="hidden" name="delivery_cost" value="{{ $deliveryCost }}">
                <input type="hidden" name="recipient_name" value="{{ $recipientName }}">
                <input type="hidden" name="recipient_phone" value="{{ $recipientPhone }}">
                <input type="hidden" name="zip_code" value="{{ $zipCode }}">
                <input type="hidden" name="county" value="{{ $county }}">
                <input type="hidden" name="district" value="{{ $district }}">
                <input type="hidden" name="address" value="{{ $address }}">
                <input type="hidden" name="country" value="{{ $country }}">
                <input type="hidden" name="country_selector_code" value="{{ $countrySelectorCode }}">
                <input type="hidden" name="cross_board_address" value="{{ $crossBoardAddress }}">
                <div class="uk-margin">
                    <div class="uk-child-width-expand uk-grid-collapse" uk-grid>
                        <div class="uk-first-column">
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                選擇運送方式
                            </div>
                        </div>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 2px solid #003a6c;">
                                選擇付款方式
                            </div>
                        </div>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                訂單確認
                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">選中的商品</h3>
                        <div class="uk-margin">
                            @foreach($selectedLots as $lot)
                                <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                                    <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                                        <img src="{{ $lot->blImages->first()->url }}" alt="" uk-cover>
                                    </div>
                                    <div class="uk-width-expand">
                                        <div class="uk-card-body" style="padding: 20px 20px">
                                            <h3 class="uk-card-title" style="margin: 0 0 10px 0">{{ $lot->name }}</h3>
                                            <p>數量: {{ $lot->cart_quantity }} | 小計: NT${{ number_format($lot->subtotal) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">請選擇付款方式</h3>
                        <ul class="uk-list">
                            <li>
                                <label><input class="uk-radio" type="radio" name="payment_method" value="1"> ATM轉帳</label>
                            </li>
                            <li>
                                <label><input class="uk-radio" type="radio" name="payment_method" value="2"> LINE Pay</label>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="uk-margin uk-align-right">
                    <button class="uk-button custom-button-1">下一步</button>
                </div>
            </form>
        </div>
    </div>
@endsection
