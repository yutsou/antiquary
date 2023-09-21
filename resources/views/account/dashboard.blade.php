@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">會員中心</h1>
    </div>
    <div class="uk-child-width-1-3@m uk-grid-small uk-grid-match" uk-grid>
        <div>
            <div class="uk-card uk-card-body custom-color-group-1">
                <h3 class="uk-card-title" style="color: #fff;">會員資料</h3>
                <ul class="uk-list uk-link-text">
                    <li><a href="{{ route('account.profile.edit') }}">帳戶設定</a></li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.seller.edit') }}">銀行帳戶設定</a></li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.password.change') }}">更改密碼</a></li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.bind.show') }}">帳號綁定</a></li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.notices.index') }}">通知</a></li>
                </ul>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-body custom-color-group-3">
                <h3 class="uk-card-title" style="color: #fff;">競標</h3>
                <ul class="uk-list uk-link-text">
                    <li><a href="{{ route('account.favorites.index') }}">感興趣的物品</a></li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.bidding_lots.index') }}">投標的物品</a></li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.orders.index') }}">待結帳的物品</a>
                        @if($ownerOrderNoticeCount != 0)
                            <span class="uk-badge" style="background-color: #003a6c;">{{ $ownerOrderNoticeCount }}</span>
                        @endif
                    </li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.orders.index') }}">已得標的物品</a>
                        @if($ownerOrderNoticeCount != 0)
                            <span class="uk-badge" style="background-color: #003a6c;">{{ $ownerOrderNoticeCount }}</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-body custom-color-group-4">
                <h3 class="uk-card-title" style="color: #fff;">委賣物品</h3>
                <ul class="uk-list uk-link-text">
                    <li><a href="{{ route('account.applications.create') }}">提交申請</a></li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.applications.index') }}">審核中的申請</a>
                        @if($ownerApplicationNoticeCount != 0)
                            <span class="uk-badge" style="background-color: #003a6c;">{{ $ownerApplicationNoticeCount }}</span>
                        @endif
                    </li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.selling_lots.index') }}">正在委賣的物品 </a>
                        @if($ownerSellingLotNoticeCount != 0)
                            <span class="uk-badge" style="background-color: #003a6c;">{{ $ownerSellingLotNoticeCount }}</span>
                        @endif
                    </li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.finished_lots.index') }}">完成委賣的物品</a></li>
                    <li>
                        <hr>
                    </li>
                    <li><a href="{{ route('account.returned_lots.index') }}">退回的物品</a>
                        @if($ownerReturnedLotNoticeCount != 0)
                            <span class="uk-badge" style="background-color: #003a6c;">{{ $ownerReturnedLotNoticeCount }}</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
