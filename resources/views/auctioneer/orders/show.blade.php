@extends('layouts.auctioneer')
@inject('methodPresenter', 'App\Presenters\MethodPresenter')
@inject('orderStatusPresenter', 'App\Presenters\OrderStatusPresenter')
@inject('orderActionPresenter', 'App\Presenters\AuctioneerOrderActionPresenter')
@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body">
            <h1 class="uk-card-title">訂單#{{ $order->id }} - {{ $orderStatusPresenter->present($order) }}</h1>
            <div uk-grid>
                <div class="uk-width-1-1">
                    <table class="uk-table uk-table-divider">
                        <tbody>
                        <tr>
                            <td class="uk-table-expand">得標者姓名</td>
                            <td class="uk-table-expand">{{ $order->user->name }}</td>
                            <td class="uk-table-expand">得標者電話</td>
                            <td class="uk-table-expand">{{ $order->user->phone }}</td>
                        </tr>
                        <tr>
                            <td class="uk-table-expand">付款方式</td>
                            <td class="uk-table-expand">{{ $methodPresenter->transferPaymentMethod($order->payment_method) }}</td>
                            <td class="uk-table-expand">付款截止時間</td>
                            <td class="uk-table-expand">{{ $order->payment_due_at }}</td>
                        </tr>
                        <tr>
                            <td class="uk-width-small">運送方式</td>
                            <td class="uk-table-expand">{{ $methodPresenter->transferDeliveryMethod($order->delivery_method) }}</td>
                        </tr>
                        <tr>
                            <td class="uk-table-expand">收貨人/取貨人 姓名</td>
                            <td class="uk-table-expand">{{ $logisticInfo->addressee_name ?? '' }}</td>
                            <td class="uk-table-expand">收貨人/取貨人 電話</td>
                            <td class="uk-table-expand">{{ $logisticInfo->addressee_phone ?? '' }}</td>
                        </tr>
                        @if($order->delivery_method > 0)
                            @switch($order->delivery_method)
                                @case(1)
                                    <tr>
                                        <td class="uk-table-expand">宅配地址</td>
                                        <td class="uk-table-expand">{{ $logisticInfo->delivery_zipcode }}{{ $logisticInfo->delivery_address }}</td>
                                    </tr>
                                    @break
                                @case(2)
                                    <tr>
                                        <td class="uk-width-small">宅配國家</td>
                                        <td class="uk-table-expand">{{ strtoupper($logisticInfo->cross_board_delivery_country_code) }} - {{ $logisticInfo->cross_board_delivery_country }}</td>

                                    </tr>
                                    <tr>
                                        <td class="uk-width-small">境外宅配地址</td>
                                        <td class="uk-table-expand">{{ $logisticInfo->cross_board_delivery_address }}</td>
                                    </tr>
                                    @break
                            @endswitch
                            @if($order->status > 0)
                                <tr>
                                    <td class="uk-width-small">物流公司</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->company_name }}</td>
                                    <td class="uk-width-small">物流追蹤碼</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->tracking_code }}</td>
                                </tr>
                            @endif
                        @endif
                        </tbody>
                    </table>
                    <table class="uk-table uk-table-divider">
                        <thead>
                        <tr>
                            <th class="uk-table-expand">物品</th>
                            <th class="uk-table-expand">得標價格</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{ $lot->name }}</td>
                            <td>NT${{ number_format($lot->current_bid) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div uk-grid>
                <div class="uk-width-2-3">
                    <label>備註：</label>
                    <div class="uk-margin">
                        <p>{{ $order->remark }}</p>
                    </div>
                </div>
                <div class="uk-width-expand">
                    <table class="uk-table uk-text-right">
                        <tbody>
                        <tr>
                            <td>小計：</td>
                            <td>NT${{ number_format($lot->current_bid) }}</td>
                        </tr>
                        <tr>
                            <td>手續費：</td>
                            <td>NT${{ number_format($order->premium) }}</td>
                        </tr>
                        <tr>
                            <td>運費：</td>
                            @if($order->delivery_cost == '')
                                <td>等待確認</td>

                            @else
                                <td>NT${{ number_format($order->delivery_cost) }}</td>
                            @endif
                        </tr>

                        <tr>
                            <td>總計：</td>
                            @if($order->total == '')
                                <td>等待確認</td>
                            @else
                                <td>NT${{ number_format($order->total) }}</td>

                            @endif
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div>
                <table class="uk-table uk-table-divider">
                    <tbody>
                    <tr>
                        <td class="uk-table-expand">委賣人姓名</td>
                        <td class="uk-table-expand">{{ $lot->owner->name }}</td>
                        <td class="uk-table-expand">委賣人電話</td>
                        <td class="uk-table-expand">{{ $lot->owner->phone }}</td>
                    </tr>
                    <tr>
                        <td class="uk-table-expand">銀行名稱</td>
                        <td class="uk-table-expand">{{ $lot->owner->bank_name }}</td>
                        <td class="uk-table-expand">分行名稱</td>
                        <td class="uk-table-expand">{{ $lot->owner->bank_branch_name }}</td>
                    </tr>
                    <tr>
                        <td class="uk-table-expand">帳號</td>
                        <td class="uk-table-expand">{{ $lot->owner->bank_account_number }}</td>
                        <td class="uk-table-expand">戶名</td>
                        <td class="uk-table-expand">{{ $lot->owner->bank_account_name }}</td>
                    </tr>
                    <tr>
                        <td class="uk-table-expand">匯款金額</td>
                        <td class="uk-table-expand">NT${{ number_format($order->owner_real_take) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <hr>
            <div uk-grid>
                <div class="uk-width-1-2"></div>
                <div class="uk-width-expand uk-text-small">
                    {!! $orderStatusPresenter->auctioneerOrderShowRecordTable($order) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="uk-margin uk-align-right">
        {!! $orderActionPresenter->present($order) !!}
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/countrySelect/css/countrySelect.css') }}">
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('js/orderAction.js') }}?v=05"></script>
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
@endpush
