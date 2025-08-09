@extends('layouts.member')
@inject('methodPresenter', 'App\Presenters\MethodPresenter')
@inject('orderStatusPresenter', 'App\Presenters\OrderStatusPresenter')
@inject('memberOrderActionPresenter', 'App\Presenters\MemberOrderActionPresenter')

@section('content')
    @if (session('notification'))
        <script>
            $(function () {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: '{{ session('notification') }}',
                    showConfirmButton: false,
                    timer: 1500
                })
            });
        </script>
    @endif
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.orders.index') }}" class="custom-color-1 custom-link-mute">已得標的物品</a> > <a href="" class="custom-color-1 custom-link-mute">訂單#{{ $order->id }}</a>
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
                    <table class="uk-table uk-table-divider uk-table-responsive">
                        <tbody>
                        <tr>
                            <td class="uk-width-small">付款方式：</td>
                            <td class="uk-table-expand">{{ $methodPresenter->transferPaymentMethod($order->payment_method) }}</td>
                            <td class="uk-width-small">付款截止時間：</td>
                            <td class="uk-table-expand">{{ $order->payment_due_at }}</td>
                        </tr>
                        <tr>
                            <td class="uk-width-small">運送方式：</td>
                            <td class="uk-table-expand">{{ $methodPresenter->transferDeliveryMethod($order->delivery_method) }}</td>
                        </tr>
                        <tr>
                            <td class="uk-width-small">收貨人/取貨人 姓名：</td>
                            <td class="uk-table-expand">{{ $logisticInfo->addressee_name ?? '' }}</td>
                            <td class="uk-width-small">收貨人/取貨人 電話：</td>
                            <td class="uk-table-expand">{{ $logisticInfo->addressee_phone ?? '' }}</td>
                        </tr>
                        @switch($order->delivery_method)
                            @case(1)
                                <tr>
                                    <td class="uk-width-small">郵遞區號：</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->delivery_zip_code }}</td>
                                    <td class="uk-width-small">縣市：</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->county }}</td>
                                </tr>
                                <tr>
                                    <td class="uk-width-small">區：</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->district }}</td>
                                    <td class="uk-width-small">街道地址：</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->delivery_address }}</td>
                                </tr>
                                @break
                            @case(2)
                                <tr>
                                    <td class="uk-width-small">宅配國家：</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->cross_board_delivery_country }}</td>

                                </tr>
                                <tr>
                                    <td class="uk-width-small">境外宅配地址：</td>
                                    <td class="uk-table-expand">{{ $logisticInfo->cross_board_delivery_address }}</td>
                                </tr>
                                @break
                        @endswitch
                        @if($order->process_status > 2 && $order->delivery_method > 0)
                            <tr>
                                <td class="uk-width-small">物流公司：</td>
                                <td class="uk-table-expand">{{ $logisticInfo->company_name }}</td>
                                <td class="uk-width-small">物流追蹤碼：</td>
                                <td class="uk-table-expand">{{ $logisticInfo->tracking_code }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    <table class="uk-table uk-table-divider">
                        <thead>
                        <tr>
                            <th>物品</th>
                            <th>價格</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orderItems as $orderItem)
                        <tr>
                            @if($orderItem->lot->type == '0')
                                <td><a href="{{ route("mart.lots.show", $orderItem->lot) }}" class="custom-link">{{ $orderItem->lot->name }}</a></td>
                            @else
                                <td><a href="{{ route("mart.products.show", $orderItem->lot) }}" class="custom-link">{{ $orderItem->lot->name }}</a></td>
                            @endif

                            <td>NT${{ number_format($orderItem->subtotal) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
            <div uk-grid>
                <div class="uk-width-2-3@m">
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
                            <td>NT${{ number_format($order->subtotal) }}</td>
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
        </div>
    </div>
    <div class="uk-margin uk-flex uk-flex-right">
        {!! $memberOrderActionPresenter->showPresent($order) !!}
    </div>

@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('js/orderAction.js') }}"></script>
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
@endpush
