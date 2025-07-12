@extends('layouts.member')
@inject('methodPresenter', 'App\Presenters\MethodPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a>
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
            <form class="uk-form-stacked" method="POST" action="{{ route('account.products.confirm', $lot) }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="number" name="payment_method" value="{{ $paymentMethod }}" hidden>
                <input type="number" name="delivery_method" value="{{ $deliveryMethod }}" hidden>
                <input type="number" name="delivery_cost" value="{{ $deliveryCost }}" hidden>
                <input type="number" name="subtotal" value="{{ $subtotal }}" hidden>
                <input type="number" name="total" value="{{ $total }}" hidden>
                <input type="text" name="recipient_name" value="{{ $recipientName }}" hidden>
                <input type="tel" name="recipient_phone" pattern="[0-9]{3}[0-9]{3}[0-9]{4}" value="{{ $recipientPhone }}"
                       hidden>
                <input type="text" name="recipient_name" value="{{ $recipientName }}" hidden>
                <input type="text" name="delivery_zip_code" value="{{ $zipcode ?? null }}" hidden>
                <input type="text" name="delivery_address"
                       value="{{ $county ?? null }}{{ $district ?? null }}{{ $address ?? null }}" hidden>
                <input type="text" name="cross_board_delivery_country" value="{{ $country ?? null }}" hidden>
                <input type="text" name="cross_board_delivery_country_code" value="{{ $countryCode ?? null }}" hidden>
                <input type="text" name="cross_board_delivery_address" value="{{ $crossBoardAddress ?? null }}" hidden>


                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h1 class="uk-card-title">商品#{{ $lot->id }}</h1>
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
                                    @switch($deliveryMethod)
                                        @case(1)
                                            <tr>
                                                <td class="uk-width-small">宅配地址：</td>
                                                <td class="uk-table-expand">{{ $zipcode }}{{ $county }}{{ $district }}{{ $address }}</td>
                                            </tr>
                                            @break
                                        @case(2)
                                            <tr>
                                                <td class="uk-width-small">宅配國家：</td>
                                                <td class="uk-table-expand">{{ $country }}</td>

                                            </tr>
                                            <tr>
                                                <td class="uk-width-small">境外宅配地址：</td>
                                                <td class="uk-table-expand">{{ $crossBoardAddress }}</td>
                                            </tr>
                                            @break
                                    @endswitch
                                    </tbody>
                                </table>
                                <table class="uk-table uk-table-divider">
                                    <thead>
                                    <tr>
                                        <th>物品</th>
                                        <th>得標價格</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>{{ $lot->name }}</td>
                                        <td>NT${{ number_format($lot->reserve_price) }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
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
                                        <td>總計：</td>
                                        <td>NT${{ number_format($total) }}</td>
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
