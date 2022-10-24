@extends('layouts.member')
@inject('methodPresenter', 'App\Presenters\MethodPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.orders.index') }}" class="custom-color-1 custom-link-mute">已得標的物品</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body">
            <h1 class="uk-card-title">訂單#{{ $order->id }}</h1>
            <ul class="uk-list">
                <li>銀行名稱 - 台新銀行</li>
                <li>分行名稱 - 桃園分行</li>
                <li>銀行代號 - 812</li>
                <li>帳號 - 20111050104837</li>
                <li>戶名 - Jason拍賣行</li>
                <li>匯款金額 - NT${{ number_format($order->total) }}</li>
                <li>匯款期限 - {{ $order->payment_due_at }}</li>
            </ul>
            <p>
                請於匯款期限前完成匯款，匯完款於本頁面下方輸入匯款帳號後五碼，並點選"通知已付款"。
            </p>
        </div>
    </div>
    <div class="uk-margin uk-flex uk-flex-right">
        <form method="POST" action="{{ route('account.atm_pay.notice', ['orderId'=>$order->id]) }}">
            @csrf
            <div class="uk-grid-small" uk-grid>
                <div>
                    <input type="text" class="uk-input uk-form-width-medium" name="account_last_five_number"
                           placeholder="帳號後五碼">
                </div>
                <div>
                    <button class="uk-button custom-button-1">通知已匯款</button>
                </div>
            </div>
        </form>
    </div>
@endsection
