@extends('layouts.member')
@inject('methodPresenter', 'App\Presenters\MethodPresenter')
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

            <form class="uk-form-stacked" method="POST" action="{{ route('account.cart.confirm') }}"
                  enctype="multipart/form-data">
                @csrf
                @foreach($selectedLotIds as $lotId)
                    <input type="hidden" name="selected_lots[]" value="{{ $lotId }}">
                @endforeach

                <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">
                <input type="hidden" name="delivery_method" value="{{ $deliveryMethod }}">
                <input type="hidden" name="delivery_cost" value="{{ $deliveryCost }}">
                <input type="hidden" name="subtotal" value="{{ $subtotal }}">
                <input type="hidden" name="total" value="{{ $total }}">
                <input type="hidden" name="recipient_name" value="{{ $recipientName }}">
                <input type="hidden" name="recipient_phone" value="{{ $recipientPhone }}">
                <input type="hidden" name="zip_code" value="{{ $zipCode ?? null }}">
                <input type="hidden" name="county" value="{{ $county ?? null }}">
                <input type="hidden" name="district" value="{{ $district ?? null }}">
                <input type="hidden" name="address" value="{{ $address ?? null }}">
                <input type="hidden" name="country" value="{{ $country ?? null }}">
                <input type="hidden" name="country_selector_code" value="{{ $countrySelectorCode ?? null }}">
                <input type="hidden" name="cross_board_address" value="{{ $crossBoardAddress ?? null }}">

                <div class="uk-margin">
                    <div class="uk-child-width-expand uk-grid-collapse" uk-grid>
                        <div class="uk-first-column">
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                選擇付款方式
                            </div>
                        </div>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                選擇運送方式
                            </div>
                        </div>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 2px solid #003a6c;">
                                訂單確認
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h1 class="uk-card-title">訂單資訊</h1>
                        <div uk-grid>
                            <div class="uk-width-1-1">
                                <table class="uk-table uk-table-divider uk-table-responsive">
                                    <tbody>
                                    <tr>
                                        <td class="uk-width-auto">付款方式：</td>
                                        <td class="uk-table-expand">{{ $methodPresenter->transferPaymentMethod($paymentMethod) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="uk-width-auto">運送方式：</td>
                                        <td class="uk-table-expand">{{ $methodPresenter->transferDeliveryMethod($deliveryMethod) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="uk-width-auto">收貨人/取貨人 姓名：</td>
                                        <td class="uk-table-expand">{{ $recipientName }}</td>
                                        <td class="uk-width-auto">收貨人/取貨人 電話：</td>
                                        <td class="uk-table-expand">{{ $recipientPhone }}</td>
                                    </tr>
                                    @if(isset($zipCode) && isset($county) && isset($district) && isset($address))
                                        <tr>
                                            <td class="uk-width-small">宅配地址：</td>
                                            <td class="uk-table-expand">{{ $zipCode }}{{ $county }}{{ $district }}{{ $address }}</td>
                                        </tr>
                                    @endif
                                    @if(isset($country) && isset($crossBoardAddress))
                                        <tr>
                                            <td class="uk-width-small">宅配國家：</td>
                                            <td class="uk-table-expand">{{ $country }}</td>
                                        </tr>
                                        <tr>
                                            <td class="uk-width-small">境外宅配地址：</td>
                                            <td class="uk-table-expand">{{ $crossBoardAddress }}</td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">商品清單</h3>
                        <table class="uk-table uk-table-divider">
                            <thead>
                            <tr>
                                <th>商品</th>
                                <th>數量</th>
                                <th>單價</th>
                                <th>運送方式</th>
                                <th>運費</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($selectedLots as $lot)
                                @php
                                    $deliveryMethodModel = $lot->deliveryMethods->where('code', $deliveryMethod)->first();
                                    $itemDeliveryCost = $deliveryMethodModel ? $deliveryMethodModel->cost : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="uk-grid-small" uk-grid>
                                            <div class="uk-width-auto">
                                                <img src="{{ $lot->blImages->first()->url }}" alt="" style="width: 60px; height: 60px; object-fit: cover;">
                                            </div>
                                            <div class="uk-width-expand">
                                                <h4 class="uk-margin-remove">{{ $lot->name }}</h4>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $lot->cart_quantity }}</td>
                                    <td>NT${{ number_format($lot->reserve_price) }}</td>
                                    <td>{{ $methodPresenter->transferDeliveryMethod($deliveryMethod) }}</td>
                                    <td>NT${{ number_format($itemDeliveryCost) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <div uk-grid>
                            <div class="uk-width-2-3@m">
                                <label>備註：</label>
                                <div class="uk-margin">
                                    <textarea class="uk-textarea" rows="4" name="remark"></textarea>
                                </div>
                            </div>
                            <div class="uk-width-expand">
                                <table class="uk-table uk-text-right">
                                    <tbody>
                                    <tr>
                                        <td>小計：</td>
                                        <td>NT${{ number_format($subtotal) }}</td>
                                    </tr>
                                    <tr>
                                        <td>運費：</td>
                                        <td>NT${{ number_format($deliveryCost) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>總計：</strong></td>
                                        <td><strong>NT${{ number_format($total) }}</strong></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uk-margin uk-flex uk-flex-right">
                    <button class="uk-button custom-button-1 uk-width-auto@s">前往付款</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/countrySelect/css/countrySelect.css') }}">
@endpush
