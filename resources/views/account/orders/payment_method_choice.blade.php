@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.orders.index') }}" class="custom-color-1 custom-link-mute">已得標的物品</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            <form class="uk-form-stacked" method="POST" action="{{ route('account.orders.update', $orderId) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="uk-margin">
                    <div class="uk-child-width-expand uk-grid-collapse" uk-grid>
                        <div class="uk-first-column">
                            <div class="uk-text-center" style="border-bottom: 2px solid #003a6c;">
                                選擇付款方式
                            </div>
                        </div>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                選擇交易方式
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
                        <h3 class="uk-card-title">您得標的物品提供以下的付款方式</h3>
                        <ul class="uk-list">
                            <li>
                                <label><input class="uk-radio" type="radio" name="paymentMethod" value="0"> 信用卡付款</label>
                            </li>
                            <li>
                                <label><input class="uk-radio" type="radio" name="paymentMethod" value="1"> ATM轉帳</label>
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
