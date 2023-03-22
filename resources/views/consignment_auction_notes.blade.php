@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <p>我們歡迎任何人提供收藏來Antiquary拍賣！<br>
            只要您提供的物品是古董或特殊的舊物，各種不尋常的物品。
        </p>
        <p>
            首先需要拍攝一系列清楚的物品照片{最低數量為5張}。
            填寫拍品的資訊說明與介紹，發送給我們。
        </p>
        <p>
            專家們會在審核後通知您。
        </p>
        <ul class="uk-list uk-list-bullet">
            <li>審核不通過 (信件中會說明原因)</li>
            <li>審核通過 (寄送物品到 Antiquary 待拍)</li>
        </ul>
    </div>
@endsection

