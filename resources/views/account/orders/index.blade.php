@extends('layouts.member')
@inject('orderStatusPresenter', 'App\Presenters\OrderStatusPresenter')
@inject('memberOrderActionPresenter', 'App\Presenters\MemberOrderActionPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            @foreach($orders as $order)
                <div class="uk-card uk-card-default uk-card-hover uk-grid-collapse uk-margin custom-card-click" orderId="{{ $order->id }}" uk-grid>
                    <div class="uk-card-media-left uk-cover-container uk-width-1-3 uk-width-1-5@m">
                        <img src="{{ $order->lot->images->first()->url }}" alt="" uk-cover>
                    </div>
                    <div class="uk-width-expand">

                        <div class="uk-card-body" style="padding: 20px 20px">
                            <div class="uk-margin uk-text-right">
                                <label>
                                    {{ $orderStatusPresenter->present($order) }}
                                </label>
                            </div>
                            <hr>
                            <h3 class="uk-card-title" style="margin: 0 0 0 0">ID.{{ $order->lot->id }}
                                - {{ $order->lot->name }}</h3>
                            <hr>
                            <div>
                                <div class="uk-grid-small uk-child-width-1-2@s" uk-grid>
                                    <div>
                                        <span>
                                            以 NT${{ number_format($order->lot->current_bid) }} 得標
                                        </span>
                                    </div>
                                    <div>
                                        <div class="uk-flex uk-flex-right">
                                            {!! $memberOrderActionPresenter->indexPresent($order) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@endsection
@push('scripts')
    <script src="{{ asset('js/orderAction.js') }}?v=00"></script>
    <script>
        $(function () {
            $('.custom-card-click').on('click', function() {
                let orderId = $(this).attr('orderId');
                window.location.assign('/account/orders/'+orderId);
            });
        });
    </script>
@endpush
