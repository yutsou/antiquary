@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <p class="uk-h3">Antiquary 是一個安全的線上競標網站</p>
        <ul class="uk-list uk-list-bullet">
            <li>只有精選的拍賣品才能進入Antiquary。</li>
            <li>所有物品均由專家仔細檢查、描述和拍照。</li>
            <li>所有拍品在發布前都經過 Antiquary 的雙重檢查。</li>
            <li>賣家不得對自己的物品進行投標。我們積極監控投標使用者。</li>
            <li>所有拍品都有相同的佣金率，在您確認出價之前，您會看到包括費用在內的總金額。</li>
            <li>所有拍品都有標示運費，在您出價之前顯示。</li>
            <li>在拍賣會上購買保證是有趣和令人興奮的！</li>
        </ul>
    </div>
@endsection

