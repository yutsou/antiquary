@extends('layouts.member')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.selling_lots.index') }}" class="custom-color-1 custom-link-mute">正在委賣的物品</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body">
            {!! $ecpayForm !!}
        </div>
    </div>
    <script>
        // 自動送出 Ecpay 表單
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('form[action*="ecpay.com.tw"]');
            if(form) form.submit();
        });
    </script>
@endsection
