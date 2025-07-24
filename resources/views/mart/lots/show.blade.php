@extends('layouts.member')
@inject('carbonPresenter', 'App\Presenters\CarbonPresenter')

@section('content')
    @if ($errors->any())
        <script>
            $(function () {
                UIkit.notification({
                    message: '<div class="uk-text-center"><span uk-icon=\'icon:  warning\'></span> {{ $errors->first() }}</div>',
                    status: 'warning',
                    pos: 'top-center',
                    timeout: 2000
                });
            });
        </script>
    @endif
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a
            href="{{ route('mart.m_categories.show', $mCategory->id) }}"
            class="custom-color-1 custom-link-mute">{{ $mCategory->name }}</a> > <a
            href="{{ route('mart.s_categories.show', [$mCategory->id, $sCategory->id]) }}"
            class="custom-color-1 custom-link-mute">{{ $sCategory->name }}</a> > <a href="{{ URL::current() }}"
                                                                                    class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin">
        <div>
            <label style="font-size: 14px">商品號碼: {{ $lot->id }}</label>
        </div>
        <div class="uk-grid-medium" uk-grid>
            <div class="uk-width-2-3@m">
                <div class="uk-text-center"><h1 class="lot-head" style="color: #333;">{{ $head }}</h1></div>
                <div class="uk-position-relative" uk-slideshow="">
                    <ul class="uk-slideshow-items">
                        @foreach($lot->blImages as $index=>$image)
                            <li>
                                <div id="ex{{$index}}" class="modal lot-modal">
                                    <div class="uk-flex uk-flex-center">
                                        <img src="{{ $image->url }}" alt="" class="lot-modal-img">
                                    </div>
                                </div>
                                <a href="#ex{{$index}}" rel="modal:open">
                                    <img src="{{ $image->url }}" alt="" style="height: 100%;" class="uk-align-center">
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="uk-margin">
                        <div class="uk-flex uk-flex-center">
                            <div style="overflow-x: scroll; height: 120px;">
                                <ul class="uk-thumbnav uk-slider-items uk-grid-small" uk-grid
                                    style="touch-action: auto !important;">
                                    @foreach($lot->blImages as $key=>$image)
                                        <li uk-slideshow-item="{{ $key }}">
                                            <a href="#">
                                                <img src="{{ $image->url }}" alt=""
                                                     style="width: auto; height: 100px; object-fit: cover;">
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-margin-medium">
                    <div class="uk-text-right">
                        @if(Auth::check())
                            <a class="custom-link" id="favorite">
                                @if(Auth::user()->getFavoriteAttribute($lot->id) == false)
                                    <span id="favoriteStatus" class="google-icon">
                                        <span class="material-symbols-outlined uk-text-middle">favorite</span>
                                        <span id="favoriteStatusText" class="uk-text-middle">加到追蹤清單</span>
                                    </span>
                                @else
                                    <span id="favoriteStatus" class="google-icon-fill">
                                        <span class="material-symbols-outlined uk-text-middle">favorite</span>
                                        <span id="favoriteStatusText" class="uk-text-middle">已加到追蹤清單</span>
                                    </span>
                                @endif
                            </a>
                        @else
                            <!-- Modal HTML embedded directly into document -->
                            <div id="favorite-login-notice" class="modal">
                                <p class="uk-text-left uk-text-large">物品加入追蹤清單前需要先登入</p>
                                <p class="uk-text-right">
                                    <a class="uk-button custom-button-1"
                                    href="{{ route('login.show', ['redirectUrl'=> route('mart.lots.show', $lot->id)]) }}">登入</a>                                </p>
                            </div>

                            <!-- Link to open the modal -->
                            <a href="#favorite-login-notice" class="custom-link" rel="modal:open">
                                <span id="favoriteStatus" class="google-icon">
                                    <span class="material-symbols-outlined uk-text-middle">favorite</span>
                                    <span id="favoriteStatusText" class="uk-text-middle">加到追蹤清單</span>
                                </span>
                            </a>
                        @endif

                    </div>
                </div>
                <div class="uk-visible@m">
                    <div class="uk-margin">
                        <div class="custom-color-group-1">
                            <h3 style="color: #fff; padding: 0.5em 0 0.5em 1em;">商品規格</h3>
                        </div>
                        <table class="uk-table">
                            <tbody>
                            <tr>
                                <td class="uk-width-1-5 uk-text-nowrap">分類</td>
                                <td>{{ $lot->main_category->name }}</td>
                            </tr>
                            @foreach($lot->specifications as $specification)
                                <tr>
                                    <td class="uk-text-nowrap">{{ $specification->title }}</td>
                                    <td>{{ $specification->value }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="uk-margin">
                        <div class="custom-color-group-1"><h3 style="color: #fff; padding: 0.5em 0 0.5em 1em;">
                                商品詳情</h3>
                        </div>
                        <div style="box-sizing: border-box; border-right: 1em #fff solid; border-left: 1em #fff solid">
                            <p>
                                {!! nl2br($lot->description) !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="uk-width-expand@m">
                <div class="uk-text-center custom-color-group-1"><h3 style="color: #fff; padding: 0.5em 0 0.5em 0;">
                        競標資訊</h3></div>
                <div class="uk-flex uk-flex-center">
                    <div style="box-sizing: border-box; border-right: 1em #fff solid; border-left: 1em #fff solid">
                        <div class="uk-margin">
                            <div class="uk-flex uk-flex-center">
                                @if($carbon->gt($lot->auction_end_at))
                                    <span class="custom-font-medium">競標已結束</span>
                                @else
                                    <span class="custom-font-medium" id="auction-end-title" hidden>競標已結束</span>
                                    @if($carbon->between($lot->auction_start_at, $lot->auction_end_at))
                                        <input id="auction-status" value="1" hidden>
                                        <div class="uk-grid-small uk-child-width-auto" id="lot-countdown" uk-grid
                                             end-at="{{ $lot->auction_end_at->toIso8601ZuluString('millisecond') }}"
                                             auction-end-at="{{ $lot->auction_end_at->toIso8601ZuluString('millisecond') }}">
                                            @else
                                                <input id="auction-status" value="2" hidden>
                                                <div class="uk-grid-small uk-child-width-auto" uk-grid hidden>
                                                    @endif

                                                    <div>將於</div>
                                                    <div>
                                                        <div class="uk-countdown-number countdown-days"
                                                             style="font-size: 1em"></div>
                                                        <div
                                                            class="uk-countdown-label uk-margin-small uk-text-center uk-visible@s"
                                                            style="font-size: 1em">天
                                                        </div>
                                                    </div>
                                                    <div class="uk-countdown-separator" style="font-size: 1em">:</div>
                                                    <div>
                                                        <div class="uk-countdown-number countdown-hours"
                                                             style="font-size: 1em"></div>
                                                        <div
                                                            class="uk-countdown-label uk-margin-small uk-text-center uk-visible@s"
                                                            style="font-size: 1em">時
                                                        </div>
                                                    </div>
                                                    <div class="uk-countdown-separator" style="font-size: 1em">:</div>
                                                    <div>
                                                        <div class="uk-countdown-number countdown-minutes"
                                                             style="font-size: 1em"></div>
                                                        <div
                                                            class="uk-countdown-label uk-margin-small uk-text-center uk-visible@s"
                                                            style="font-size: 1em">分
                                                        </div>
                                                    </div>
                                                    <div class="uk-countdown-separator" style="font-size: 1em">:</div>
                                                    <div>
                                                        <div class="uk-countdown-number countdown-seconds"
                                                             style="font-size: 1em"></div>
                                                        <div
                                                            class="uk-countdown-label uk-margin-small uk-text-center uk-visible@s"
                                                            style="font-size: 1em">秒
                                                        </div>
                                                    </div>
                                                    <div>後結束競標</div>
                                                </div>
                                            @endif
                                        </div>
                                        <hr>
                                        <div class="uk-margin">
                                            <ul>
                                                @if($lot->deliveryMethods->pluck('code')->contains(0))
                                                    <li>提供面交</li>
                                                @else
                                                    <li>
                                                        <del>提供面交</del>
                                                    </li>
                                                @endif
                                                @if($lot->deliveryMethods->pluck('code')->contains(1))
                                                    <li>提供宅配 -
                                                        得標者需支付運費NT${{ number_format($lot->getHomeDeliveryAttribute()->cost) }}</li>
                                                @else
                                                    <li>
                                                        <del>提供宅配</del>
                                                    </li>
                                                @endif
                                                @if($lot->deliveryMethods->pluck('code')->contains(2))
                                                    <li>提供境外宅配 -
                                                        得標者需支付運費NT${{ number_format($lot->getCrossBorderDeliveryAttribute()->cost) }}</li>
                                                @else
                                                    <li>
                                                        <del>提供境外宅配</del>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>

                                        <div class="uk-margin">
                                            <label style="font-size: 14px">拍賣手續費: {{ $premium }}</label>
                                            <br>
                                            @if($lot->estimated_price != 0)
                                                <label style="font-size: 14px">預估價格 - NT$<span
                                                        id="estimatedPrice">{{ number_format($lot->estimated_price) }}</span></label>
                                                <br>
                                            @endif
                                            @if($lot->starting_price != 0)
                                                <label style="font-size: 14px">起標價格 -
                                                    NT$<span>{{ number_format($lot->starting_price) }}</span></label>
                                            @endif
                                            <input id="starting-price" value="{{ $lot->starting_price }}" hidden>
                                        </div>

                                        <hr>

                                        <div class="uk-margin uk-flex uk-flex-center">
                                            <label>目前價格: NT$<span
                                                    id="currentBid">{{ number_format($lot->current_bid) }}</span></label>
                                        </div>
                                        <hr>
                                        <div class="uk-margin">
                                            <div class="uk-grid-small" uk-grid>
                                                <div>
                                                    <input id="bidInput" hidden>
                                                    <div id="next-bids-field" class="uk-width-1-1"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="uk-margin">
                                            <div class="uk-grid-small" uk-grid>
                                                <div class="uk-width-expand">
                                                    <input id="autoBidInput" class="uk-input">
                                                </div>
                                                <div class="uk-width-auto">
                                                    @auth
                                                        <div id="confirmAutoBid" class="modal">
                                                            <h3 id="confirmAutoBidTitle"></h3>
                                                            <p class="uk-text-left">出價金額不包含運費及拍賣服務費用。</p>
                                                            <p class="uk-text-right">
                                                                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                                                <a id="autoBid" class="uk-button custom-button-1">確認</a>
                                                            </p>
                                                        </div>
                                                        <a href="#confirmAutoBid" id="confirmAutoBidButton" class="uk-button custom-button-1 uk-width-expand">設定</a>

                                                    @endauth
                                                    @guest

                                                        <!-- Modal HTML embedded directly into document -->
                                                        <div id="auto-bid-login-notice" class="modal">
                                                            <p class="uk-text-left uk-text-large">自動出價前需要先登入</p>
                                                            <p class="uk-text-right">
                                                                <a class="uk-button custom-button-1"
                                                                href="{{ route('login.show', ['redirectUrl'=> route('mart.lots.show', $lot->id)]) }}">登入</a>
                                                            </p>
                                                        </div>

                                                        <!-- Link to open the modal -->
                                                        <a href="#auto-bid-login-notice" class="uk-button custom-button-1 uk-width-expand" rel="modal:open">
                                                            設定
                                                        </a>
                                                    @endguest
                                                </div>
                                            </div>

                                            @if(Auth::check())
                                                <div
                                                    id="my-auto-bid-section" {{ Auth::user()->getLotAutoBid($lot->id) === null ? "hidden" : "" }}>
                                                    <label style="font-size: 0.9em">您的自動出價 - NT$
                                                        <span
                                                            id="my-auto-bid">{{ number_format(optional(Auth::user()->getLotAutoBid($lot->id))->bid) ?? '' }}</span></label>
                                                </div>
                                            @endif


                                        </div>
                                        <div class="uk-overflow-auto" style="max-height: 30vh;">
                                            <table class="uk-table">
                                                <tbody id="bidHistories" style="font-size: 0.8em">
                                                @foreach($lot->bidRecords as $bidRecord)
                                                    <tr>
                                                        @if(Auth::check() && $bidRecord->bidder_id === Auth::user()->id)
                                                            <td>你</td>
                                                        @else
                                                            <td class="uk-text-nowrap">
                                                                競標者 {{ $bidRecord->bidder_alias }}</td>
                                                        @endif


                                                        <td style="font-size: 0.8em">{{ $bidRecord->created_at }}</td>
                                                        <td>NT${{ number_format($bidRecord->bid) }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="uk-hidden@m">
                <div>
                    <ul class="uk-child-width-expand" uk-tab>
                        <li class="uk-active"><a href="#">商品規格</a></li>
                        <li><a href="#">商品詳情</a></li>
                    </ul>
                    <ul class="uk-switcher uk-margin">
                        <li>
                            <table class="uk-table">
                                <tbody>
                                <tr>
                                    <td class="uk-width-1-5 uk-text-nowrap">分類</td>
                                    <td>{{ $lot->main_category->name }}</td>
                                </tr>
                                @foreach($lot->specifications as $specification)
                                    <tr>
                                        <td class="uk-text-nowrap">{{ $specification->title }}</td>
                                        <td>{{ $specification->value }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </li>
                        <li>
                            <div
                                style="box-sizing: border-box; border-right: 1em #fff solid; border-left: 1em #fff solid">
                                <p>
                                    {!! nl2br($lot->description) !!}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <hr>
        <div class="uk-margin-xlarge-top">
            <div class="uk-width-1-1">
                <div class="uk-margin-medium">
                    <h3>{{ $auction->name }}的其他物品</h3>
                </div>
                <div class="uk-slider-container-offset" uk-slider="finite: true">

                    <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1">

                        <ul class="uk-slider-items uk-child-width-1-4@l uk-child-width-1-3@m  uk-child-width-1-2@s uk-grid-small uk-grid-match uk-grid">
                            @foreach($auction->lots as $singleLot)
                                @if($singleLot->id != $lot->id)
                                    <li>
                                        <div class="uk-card uk-card-default uk-card-hover lot-card-click"
                                             lotId="{{ $singleLot->id }}">
                                            <div class="uk-card-media-top">
                                                <div
                                                    class="uk-background-cover uk-height-medium uk-panel uk-flex uk-flex-center uk-flex-middle"
                                                    style="background-image: url({{ $singleLot->blImages->first()->url }});">
                                                </div>
                                            </div>
                                            <div class="uk-card-body">
                                                <div class="uk-flex uk-flex-right">
                                                    @include('mart.components.favorite-inline', $singleLot)
                                                </div>
                                                <h3 class="uk-card-title uk-text-truncate custom-font-medium">{{ $singleLot->name }}</h3>
                                                <label class="custom-font-medium"
                                                       style="color: #003a6c">NT${{ number_format($singleLot->current_bid) }}</label>
                                                <p>{!! $carbonPresenter->lotPresent($singleLot->id, $singleLot->auction_end_at) !!}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>

            </div>
        </div>
        @include('mart.components.favorite-outline')
        <hr>
        <div class="uk-margin-xlarge-top">
            <div class="uk-width-1-1">
                <div class="uk-margin-medium">
                    <h3>其他拍賣會</h3>
                </div>
                <div class="uk-slider-container-offset" uk-slider="finite: true">
                    <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1">
                        <ul class="uk-slider-items  uk-child-width-1-4@l uk-child-width-1-3@m  uk-child-width-1-2@s uk-grid-small uk-grid-match uk-grid">
                            @foreach($auctions->whereIn('status', [0,1]) as $auction)
                                <li>
                                    <div class="uk-card uk-card-default uk-card-hover auction-card-click"
                                         auctionId="{{ $auction->id }}">
                                        <div class="uk-card-media-top">
                                            <img src="{{ $auction->lots->first()->blImages->first()->url }}" alt=""
                                                 style="width: 100vw; height: 300px; object-fit: cover;">
                                        </div>
                                        <div class="uk-card-body">
                                            <h3 class="uk-card-title">{{ $auction->name }}</h3>
                                            <p>{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->end_at) }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input id="bidderId" value="{{ optional(Auth::user())->id }}" hidden>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}"
          crossorigin="anonymous">
    <style>
        .uk-active > a {
            border-bottom: 2px solid #003a6c !important;
        }
    </style>
    <style>
        .lot-head {
            font-size: 2.0em;
        }

        @media (min-width: 1200px) {

            .lot-head {
                font-size: 2.5em;
            }

        }
    </style>
    <style>
        .lot-modal-img {
            max-width: 100vw;
            width: 75vw;
            max-height: 100vh;
            height: auto;
        }

        .lot-modal {
            max-width: 100vw;
            max-height: 100vh;
        }

        @media (min-width: 1200px) {
            .lot-modal-img {
                max-width: 75vw;
                width: auto;
                max-height: 75vh;
                height: 75vh;
            }
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('js/app.js') }}"></script>


    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
        class UnitDate {
            constructor(date) {
                let {userAgent} = window.navigator;
                if (userAgent.includes('Safari')) {
                    if (typeof date === 'string') {
                        date = date.replace(/-/g, '/');
                        return new Date(date);
                    }
                    return new Date(date);
                }
                return new Date(date);
            }
        }
    </script>
    <script>
        let bidRule = function (bid) {
            if (bid >= 0 && bid <= 500) {
                return 50;
            } else if (bid >= 501 && bid <= 5000) {
                return 250;
            } else if (bid >= 5001 && bid <= 10000) {
                return 500;
            } else if (bid >= 10001 && bid <= 25000) {
                return 2500;
            } else if (bid >= 25001 && bid <= 50000) {
                return 5000;
            } else if (bid >= 50001 && bid <= 250000) {
                return 10000;
            } else if (bid >= 250001 && bid <= 1000000) {
                return 50000;
            } else if (bid >= 1000001) {
                return 100000;
            }
        }

        let getNextBids = function (bid) {
            let startingPriceString = $("#starting-price").val();
            let startingPriceInt = parseInt(startingPriceString);
            let firstBid, secondBid, thirdBid = 0;

            if (bid === 0 && startingPriceInt !== 0) {
                bid = startingPriceInt;
                firstBid = bid;
                secondBid = bid + bidRule(bid);
                thirdBid = secondBid + bidRule(secondBid);
            } else {
                firstBid = bid + bidRule(bid);
                secondBid = firstBid + bidRule(firstBid);
                thirdBid = secondBid + bidRule(secondBid);
            }


            return [firstBid, secondBid, thirdBid];
        }

        let generateModalwithButton = function (bid) {
            @if(Auth::check())
                return '' +
                '<a data-modal="#confirm-manual-bid-modal-' + bid + '" class="uk-button uk-width-expand confirm-manual-bid-buttons" style="margin: 1px; color: #003a6c" bid="' + bid + '">出價NT$' + number_format(bid) + '</a>' +
                '<div id="confirm-manual-bid-modal-' + bid + '" class="modal">' +
                '<h3>確認出價 NT$' + number_format(bid) + '</h3>' +
                '<p class="uk-text-left">出價金額不包含運費及拍賣服務費用。</p>' +
                '<div class="uk-grid-small uk-flex uk-flex-right" uk-grid>' +
                '<div><a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a></div>' +
                '<div><a class="uk-button custom-button-1 bids">確認</a></div>' +
                '</div>' +
                '</div>';
            @else
                return '' +
                '<a data-modal="#confirm-manual-bid-modal-' + bid + '" class="uk-button uk-width-expand confirm-manual-bid-buttons" style="margin: 1px; color: #003a6c" bid="' + bid + '">出價NT$' + number_format(bid) + '</a>' +
                '<div id="confirm-manual-bid-modal-' + bid + '" class="modal">' +
                '<p class="uk-text-left uk-text-large">出價前需要先登入</p>' +
                '<div class="uk-flex uk-flex-right">' +
                '<div><a class="uk-button custom-button-1" href="{{ route('login.show', ['redirectUrl'=> route('mart.lots.show', $lot->id)]) }}">登入</a></div>' +
                '</div>' +
                '</div>';
            @endif

        }

        let setNextBids = function (bid) {
            let nextBids = getNextBids(bid);
            let nextBidsField = $('#next-bids-field');
            nextBidsField.empty();

            nextBids.forEach(function (bid) {
                nextBidsField.append(
                    generateModalwithButton(bid)
                );
            });

            $('#bidInput').val();
        }

        let setInputPlaceholder = function (bid) {
            let autoBidInput = $("#autoBidInput");
            let startingPriceString = $("#starting-price").val();
            let startingPriceInt = parseInt(startingPriceString);
            let text = "";

            if (bid === 0 && startingPriceInt !== 0) {
                text = "自動出價：最低 NT$" + number_format(startingPriceInt);
            } else {
                text = "自動出價：最低 NT$" + (bid + bidRule(bid)).toString();
            }

            autoBidInput.attr("placeholder", text);
        }

        let addFavorite = function (lotId) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "post",
                url: '/account/ajax/lots/' + lotId + '/favorite',
                data: {lotId: lotId},
                success: function (status) {
                    if (status === 'added') {
                        $('#favoriteStatusText').text('已加到追蹤清單');
                        $('#favoriteStatus').removeClass('google-icon').addClass('google-icon-fill');
                    } else {
                        $('#favoriteStatusText').text('加到追蹤清單');
                        $('#favoriteStatus').removeClass('google-icon-fill').addClass('google-icon');
                    }
                }
            });
        };

        let successResponse = function (success) {
            if (success.data.type === 'warning') {
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: '出價成功，但未達底價',
                    html:
                        '<p style="color: #666">' + success.data.text + '</p>',
                    showConfirmButton: false,
                    timer: 5000
                })
            } else {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: '出價成功',
                    showConfirmButton: false,
                    timer: 1500
                })
            }
        }

        let failedResponse = function (error) {
            if (error.response.data.errors.bid) {
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: error.response.data.errors.bid[0],
                    showConfirmButton: false,
                    timer: 2000
                })
            } else if (error.response.data.errors.bidTime) {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: error.response.data.errors.bidTime[0],
                    showConfirmButton: false,
                    timer: 2000
                })
            } else if (error.response.data.errors.bidderId) {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: error.response.data.errors.bidderId[0],
                    showConfirmButton: false,
                    timer: 2000
                })
            } else if (error.response.data.errors.bidderStatus) {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: error.response.data.errors.bidderStatus[0],
                    showConfirmButton: false,
                    timer: 2000
                })
            }
        }

        let bid = function (lotId, bidInput) {
            let bidderId = $('#bidderId').val();

            axios.post('/account/axios/lots/manual_bid', {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'lotId': lotId,
                'bidderId': bidderId,
                'bid': bidInput
            })
                .then(function (response) {
                    successResponse(response);
                })
                .catch(function (response) {
                    failedResponse(response);
                });
        };

        let autoBid = function (lotId, autoBidInput) {
            let bidderId = $('#bidderId').val();
            axios.post('/account/axios/lots/auto_bid', {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'lotId': lotId,
                'bidderId': bidderId,
                'bid': autoBidInput
            })
                .then(function (response) {
                    $('#my-auto-bid').text(number_format(parseInt(response.data.myAutoBid)));
                    $('#my-auto-bid-section').prop('hidden', false);
                    successResponse(response);
                })
                .catch(function (response) {
                    failedResponse(response);
                });
        };
    </script>
    <script>
        let setLotCountdown = function (countdown) {
            const second = 1000,
                minute = second * 60,
                hour = minute * 60,
                day = hour * 24;

            function freshCountdown(countdown, dueTime) {
                let now = new Date().getTime();
                let distance = dueTime - now;

                if (distance < 1000) {
                    clearInterval(timer);
                    Swal.fire({
                        position: 'center',
                        icon: 'warning',
                        title: '競標結束',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    countdown.prop('hidden', true);
                    $('#auction-end-title').prop('hidden', false);
                } else {
                    $(".countdown-days").text(Math.floor(distance / (day)).toString().padStart(2, '0'));
                    $(".countdown-hours").text(Math.floor((distance % (day)) / (hour)).toString().padStart(2, '0'));
                    $(".countdown-minutes").text(Math.floor((distance % (hour)) / (minute)).toString().padStart(2, '0'));
                    $(".countdown-seconds").text(Math.floor((distance % (minute)) / second).toString().padStart(2, '0'));
                }
            }

            let timer = setInterval(function () {
                let dueTimeIso = countdown.attr('end-at');
                let dueTime = new Date(dueTimeIso).getTime();
                freshCountdown(countdown, dueTime)
            }, 250)
        };

        $(function () {
            let lotCountdown = $('#lot-countdown');
            setLotCountdown(lotCountdown);
        });

    </script>
    <script>
        Echo.channel(`lots.{{ $lot->id }}`)
            .listen('NewBid', (e) => {
                let bidderId = $('#bidderId').val();
                let newBid = '';
                if (bidderId === e.bidderId.toString()) {
                    newBid = '<tr><td>你</td><td>' + e.created_at + '</td><td>NT$' + number_format(e.bid) + '</td></tr>';
                } else {
                    newBid = '<tr><td>競標者 ' + e.bidderAlias + '</td><td style="font-size: 0.8em">' + e.created_at + '</td><td>NT$' + number_format(e.bid) + '</td></tr>';
                }

                setNextBids(parseInt(e.bid));
                setInputPlaceholder(parseInt(e.bid));
                $('#bidHistories').prepend(newBid);
                $('#currentBid').text(number_format(e.bid));
                $('#nextBid').text(number_format(parseInt(e.bid) + bidRule(parseInt(e.bid))));


                let lotCountdown = $('#lot-countdown');
                console.log(lotCountdown.attr('auction-end-at'));
                console.log(e.auction_end_at);

                if (e.auction_end_at !== lotCountdown.attr('auction-end-at')) {
                    lotCountdown.attr('end-at', e.auction_end_at);
                    Swal.fire({
                        icon: 'info',
                        title: '拍賣時間已延長',
                        confirmButtonText: '<span style="color: #fff;">好的</span>',
                        confirmButtonColor: '#003a6c',
                    })
                }
            });
    </script>
    <script>
        $(function () {
            setNextBids({{ $lot->current_bid }});
            setInputPlaceholder({{ $lot->current_bid }})

            $("#favorite").click(function () {
                addFavorite({{ $lot->id }});
            });

            $(document).on('click', '.confirm-manual-bid-buttons', function () {
                let bid = $(this).attr('bid');
                $('#confirm-manual-bid-modal-' + bid).modal();
                $('#bidInput').val(bid);
                return false;
            });

            $("#confirmAutoBidButton").click(function (event) {
                $(this).modal();
                let bidInput = $('#autoBidInput').val();
                $('#confirmAutoBidTitle').text('確認設定自動出價 NT$' + number_format(bidInput));
                return false;
            });

            $(document).on('click', '.bids', function () {
                let bidInput = $('#bidInput')
                bid({{ $lot->id }}, bidInput.val());
                bidInput.val('');
                $.modal.close();
            });

            $("#directly-buy").click(function () {
                bid({{ $lot->id }}, {{ $lot->estimated_price }});
                $.modal.close();
            });

            $("#autoBid").click(function () {
                let autoBidInput = $('#autoBidInput')
                autoBid({{ $lot->id }}, autoBidInput.val());
                autoBidInput.val('');
                $.modal.close();
            });
        });
    </script>
    <script>
        $(function () {
            $(".lot-card-click").click(function () {
                let lotId = $(this).attr('lotId');
                let url = '{{ route("mart.lots.show", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
            });
        });
    </script>
    <script>
        $(function () {
            $('.auction-card-click').on('click', function () {
                let aucitonId = $(this).attr('auctionId');
                window.location.assign('/auctions/' + aucitonId);
            });
        });
    </script>
    <script>
        let setLotCardCountdown = function (countdown) {
            const second = 1000,
                minute = second * 60,
                hour = minute * 60,
                day = hour * 24;

            function freshCountdown(countdown, dueTime) {
                let now = new Date().getTime();

                let distance = dueTime - now;
                let days = Math.floor(distance / (day)).toString().padStart(2, '0');
                let hours = Math.floor((distance % (day)) / (hour)).toString().padStart(2, '0');
                let minutes = Math.floor((distance % (hour)) / (minute)).toString().padStart(2, '0');
                let seconds = Math.floor((distance % (minute)) / second).toString().padStart(2, '0');


                //do something later when date is reached
                if (distance < 1000) {
                    clearInterval(timer);
                    countdown.text('競標結束')
                } else {
                    if (distance > 86400000) {
                        countdown.text('於 ' + days + '天內結束競標')
                    } else {
                        countdown.text('於 ' + hours + '時' + minutes + '分' + seconds + '秒 後結束')
                    }
                }
            }

            let timer = setInterval(function () {
                let dueTimeIso = countdown.attr('end-at');
                let dueTime = new Date(dueTimeIso).getTime();
                freshCountdown(countdown, dueTime)
            }, 500)
        };

        $(function () {
            let lotCardCountdowns = $('.lot-card-countdowns');
            lotCardCountdowns.each(function () {
                let lotCardCountdown = $('#' + this.id);
                setLotCardCountdown(lotCardCountdown);
            });
        });
    </script>
@endpush
