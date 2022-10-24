@extends('layouts.auctioneer')
@inject('orderStatusPresenter', 'App\Presenters\OrderStatusPresenter')
@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body" style="background-color: #F7F7F7;">
            <div class="uk-margin">

                <div class="uk-overflow-auto uk-height-large" id="message-window">
                    @foreach($order->messages as $message)
                        @if($order->user_id !== $message->user_id)
                            <div class="uk-flex uk-flex-right">
                                <div class="uk-margin uk-card uk-card-small uk-card-default uk-card-body uk-width-1-3">賣家：{{ $message->message }}</div>
                            </div>
                        @else
                            <div class="uk-flex uk-flex-left">
                                <div class="uk-margin uk-card uk-card-small uk-card-default uk-card-body uk-width-1-3">買家：{{ $message->message }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>
        </div>
    </div>
@endsection
