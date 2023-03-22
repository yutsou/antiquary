@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <ul class="uk-list uk-list-bullet">
            <li>委賣方保證不會通過提供和出售該拍品 (i) 違反任何法律或法規，(ii) 侵犯任何第三方權利 (iii) 對買方或任何第三方進行欺詐或非法行為。</li>
            <li>委賣方對拍品的一致性、質量、安全性、合法性、出處和真實性以及拍品描述（包括照片和拍品描述）的正確性、準確性和完整性負責並承擔所有責任{包含但不限於中華民國相關法律規定}。</li>
            <li>我們對您使用我們的服務收取費用。委賣方佣金為結標價格的 15%。將從結標價格的支付中扣除。{例如：結標金額NT.1000 委賣費15% NT.150 運費100，支付給您的總金額為NT.950}，我們還因提供服務並向買方收取費用。接受這些委賣條款，即表示您明確同意我們就同一交易向委賣方和買方收取服務費用。</li>
            <li> 拍品競標售出後，經買家確認已收到該拍品後五 (5) 工作日內，拍品結標價格和運費（如有）扣除Antiquary佣金後匯款至委賣方指定帳戶。</li>
        </ul>
    </div>
@endsection

