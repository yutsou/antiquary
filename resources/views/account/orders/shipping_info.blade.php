@extends('layouts.member')
@inject('methodPresenter', 'App\Presenters\MethodPresenter')
@inject('orderStatusPresenter', 'App\Presenters\orderStatusPresenter')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.selling_lots.index') }}" class="custom-color-1 custom-link-mute">正在委賣的物品</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body">
            <h1 class="uk-card-title">訂單#{{ $order->id }}
                - {{ $orderStatusPresenter->present($order) }}</h1>
            <div uk-grid>
                <div class="uk-width-1-1">
                    <table class="uk-table uk-table-divider">
                        <tbody>
                        <tr>
                            <td class="uk-width-small">運送方式</td>
                            <td class="uk-table-expand">{{ $methodPresenter->transferDeliveryMethod($order->delivery_method) }}</td>
                        </tr>
                        <tr>
                            <td class="uk-table-expand">收貨人/取貨人 姓名</td>
                            <td class="uk-table-expand">{{ $logisticInfo->addressee_name }}</td>
                            <td class="uk-table-expand">收貨人/取貨人 電話</td>
                            <td class="uk-table-expand">{{ $logisticInfo->addressee_phone }}</td>
                        </tr>
                        @switch($order->delivery_method)
                            @case(1)
                                <tr>
                                    <td class="uk-table-expand">郵遞區號</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->delivery_zip_code }}</td>
                                    <td class="uk-table-expand">縣市</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->county }}</td>
                                </tr>
                                <tr>
                                    <td class="uk-table-expand">區</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->district }}</td>
                                    <td class="uk-table-expand">街道地址</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->delivery_address }}</td>
                                </tr>
                                @break
                            @case(2)
                                <tr>
                                    <td class="uk-width-small">宅配國家</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->cross_board_delivery_country }}</td>

                                </tr>
                                <tr>
                                    <td class="uk-width-small">境外宅配地址</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->cross_board_delivery_address }}</td>
                                </tr>
                                @break
                        @endswitch
                        @if($order->process_status > 2 && $order->delivery_method > 0)
                            <tr>
                                <td class="uk-width-small">物流公司</td>
                                <td class="uk-table-expand">{{ $logisticInfo->company_name }}</td>
                                <td class="uk-width-small">物流追蹤碼</td>
                                <td class="uk-table-expand">{{ $logisticInfo->tracking_code }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="uk-margin uk-align-right">
        @if($order->delivery_method === 0)

        @else
            @switch($order->status)
                @case(13)
                    <form method="POST" action="{{ route('account.orders.notice_shipping', $order) }}">
                        @csrf
                        <div class="uk-grid-small" uk-grid>
                            <div>
                                <input type="text" class="uk-input uk-form-width-small" name="logistics_name"
                                       placeholder="物流公司">
                            </div>
                            <div>
                                <input type="text" class="uk-input uk-form-width-medium" name="tracking_code"
                                       placeholder="物流追蹤碼">
                            </div>
                            <div>
                                <button class="uk-button custom-button-1">通知出貨</button>
                            </div>
                        </div>
                    </form>
                    @break
            @endswitch
        @endif
    </div>
@endsection
