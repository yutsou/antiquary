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
            <form action="https://n.gomypay.asia/TestShuntClass.aspx" method="post" class="uk-form-stacked">
                <input name='Send_Type' value='0' hidden>
                <input name='Pay_Mode_No' value='2' hidden>
                <input name='CustomerId' value='E8C95C73255ED798EC637705A80B35BC' hidden>
                <input name='Order_No' value='{{ $eOrderNum }}' hidden>
                <input name='TransMode' value='1' hidden>
                <input name='Amount' value='{{ intval($order->total) }}' hidden>
                <input name='Installment' value='0' hidden>
                <input name='TransCode' value='00' hidden>
                <input name='Buyer_Memo' value='{{ $order->lot->name }}' hidden>
                <input name="Callback_Url" value="{{ route('shop.pay.gomypay.callback') }}" hidden>
                <input name="Return_url" value="{{ route('shop.pay.gomypay.return') }}" hidden>
                <div class="uk-margin">
                    <label class="uk-form-label" for="buyer-name">信用卡持有人姓名</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="buyer-name" name="Buyer_Name" type="text" value="{{ $order->user->name }}">
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="buyer-phone">信用卡持有人電話</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="buyer-phone" name="Buyer_Telm" type="text" value="{{ $order->user->phone }}">
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="buyer-email">信用卡持有人信箱</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="buyer-email" name="Buyer_Mail" type="text" value="{{ $order->user->email }}">
                    </div>
                </div>

                <div class="uk-flex uk-flex-right">
                    <button type="submit" class="uk-button custom-button-1">填寫信用卡資訊</button>
                </div>
            </form>
        </div>
    </div>
@endsection
