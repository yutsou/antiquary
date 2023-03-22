@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <p>
            我們是一個綜合古董舊物線上拍賣會，我們出售古傢俬、燈飾、瓷器、水晶、藝術品、設計原創物品、裝飾品、收藏品、珍貴物品的線上拍賣市場。
        </p>
        <p>
            這些物品在網站上拍賣出售，很像 eBay，但有一個顯著區別：所有物品都經過專家仔細審核、檢查、描述和拍照，因此您可以放心出價。在這裡你不會發現仿古的贗品。
        </p>
        <p>
            在拍賣會上購買是更聰明的，對您的錢包和環境都是如此。您可以找到有趣的物品來個性化您的家 - 比大量生產的現代製品花費更少，而質量卻更好。如果您選擇再次出售它，您甚至有可能得到的比您所支付的更多，這就是拍賣的樂趣。
        </p>
    </div>
@endsection

