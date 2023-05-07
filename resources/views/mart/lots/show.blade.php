@extends('layouts.member')

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
    <div class="uk-margin">
        <div class="uk-grid-medium" uk-grid>
            <div class="uk-width-2-3@m">
                <div class="uk-text-center"><h1 class="lot-head" style="color: #333;">{{ $head }}</h1></div>
                <div class="uk-position-relative" uk-slideshow="">
                    <ul class="uk-slideshow-items">
                        @foreach($lot->images as $index=>$image)
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
                                <ul class="uk-thumbnav uk-slider-items uk-grid-small" uk-grid style="touch-action: auto !important;">
                                    @foreach($lot->images as $key=>$image)
                                        <li uk-slideshow-item="{{ $key }}">
                                            <a href="#">
                                                <img src="{{ $image->url }}" alt="" style="width: auto; height: 100px; object-fit: cover;">
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
                        @auth
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
                        @endauth
                        @guest
                            <a href="#favorite-login-notice" class="custom-link" uk-toggle>
                                 <span id="favoriteStatus" class="google-icon">
                                    <span class="material-symbols-outlined uk-text-middle">favorite</span>
                                    <span id="favoriteStatusText" class="uk-text-middle">加到追蹤清單</span>
                                </span>
                            </a>
                            <div id="favorite-login-notice" class="uk-flex-top" uk-modal>
                                <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                    <button class="uk-modal-close-default" type="button" uk-close></button>
                                    <h2 class="uk-modal-title">物品加入追蹤清單前需要先登入</h2>
                                    <div class="uk-flex uk-flex-right">
                                        <a class="uk-button custom-button-1"
                                           href="{{ route('login.show', ['redirectUrl'=>'lots_'.$lot->id]) }}">登入</a>
                                    </div>
                                </div>
                            </div>
                        @endguest
                    </div>
                </div>
                <div class="uk-visible@m">
                    <div class="uk-margin">
                        <div class="custom-color-group-1"><h3 style="color: #fff; padding: 0.5em 0 0.5em 1em;">商品規格</h3>
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
                        <div class="custom-color-group-1"><h3 style="color: #fff; padding: 0.5em 0 0.5em 1em;">商品詳情</h3>
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
                <div class="uk-text-center custom-color-group-1"><h3 style="color: #fff; padding: 0.5em 0 0.5em 0;">競標資訊</h3></div>
                <div class="uk-flex uk-flex-center">
                    <div style="box-sizing: border-box; border-right: 1em #fff solid; border-left: 1em #fff solid">
                        <div class="uk-margin">
                            <div class="uk-flex uk-flex-center">
                                @if($carbon->gt($lot->auction_end_at))
                                    <span class="custom-font-medium">競標已結束</span>
                                @else
                                    <span class="custom-font-medium" id="auction-end-title" hidden>競標已結束</span>
                                    @if($carbon->lt($lot->auction_start_at))
                                        <input id="auction-status" value="0" hidden>
                                        <div class="uk-grid-small uk-child-width-auto countdown" uk-grid
                                             end-at="{{ $lot->auction_start_at->toIso8601ZuluString('millisecond') }}" auction-end-at="{{ $lot->auction_end_at }}">
                                    @elseif($carbon->between($lot->auction_start_at, $lot->auction_end_at))
                                        <input id="auction-status" value="1" hidden>
                                        <div class="uk-grid-small uk-child-width-auto countdown" uk-grid
                                             end-at="{{ $lot->auction_end_at->toIso8601ZuluString('millisecond') }}" auction-end-at="{{ $lot->auction_end_at }}">
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
                                    @if($carbon->lt($lot->auction_start_at))
                                        <div id="auction-countdown-action">後開始競標</div>
                                    @else
                                        <div>後結束競標</div>
                                    @endif
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
                                        <hr>
                                        <div class="uk-margin">
                                            <label>預估價格 - NT$<span id="currentBid">{{ number_format($lot->estimated_price) }}</span></label>
                                        </div>
                                        <hr>
                                        <div class="uk-margin">
                                            <label>目前價格 - NT$<span id="currentBid">{{ number_format($lot->current_bid) }}</span></label>
                                        </div>
                                        <hr>
                                        <div class="uk-margin">
                                            <div class="uk-margin">
                                                <label>下一個最低出價 - NT$<span
                                                        id="nextBid">{{ number_format($lot->next_bid) }}</span></label>
                                            </div>
                                            <div class="uk-grid-small" uk-grid>
                                                <div class="uk-width-expand">
                                                    <input id="bidInput" class="uk-input" placeholder="手動出價">
                                                </div>
                                                <div class="uk-width-auto">
                                                    @auth
                                                        <div id="confirmManualBid" class="modal">
                                                            <h3 id="confirmManualBidTitle"></h3>
                                                            <p class="uk-text-right">
                                                                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                                                <a id="bid" class="uk-button custom-button-1">確認</a>
                                                            </p>
                                                        </div>
                                                        <a href="#confirmManualBid" id="confirmManualBidButton" class="uk-button custom-button-1">出價</a>
                                                    @endauth
                                                    @guest
                                                        <a class="uk-button custom-button-1" href="#bid-login-notice" uk-toggle>
                                                            出價
                                                        </a>
                                                        <div id="bid-login-notice" class="uk-flex-top" uk-modal>
                                                            <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                                                <h2 class="uk-modal-title">出價前需要先登入</h2>
                                                                <div class="uk-flex uk-flex-right">
                                                                    <a class="uk-button custom-button-1"
                                                                       href="{{ route('login.show', ['redirectUrl'=>'lots_'.$lot->id]) }}">登入</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endguest
                                                </div>
                                            </div>
                                        </div>
                                        <div class="uk-margin">
                                            <div class="uk-grid-small" uk-grid>
                                                <div class="uk-width-expand">
                                                    <input id="autoBidInput" class="uk-input" placeholder="自動出價">
                                                </div>
                                                <div class="uk-width-auto">
                                                    @auth
                                                        <div id="confirmAutoBid" class="modal">
                                                            <h3 id="confirmAutoBidTitle"></h3>
                                                            <p class="uk-text-right">
                                                                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                                                <a id="autoBid" class="uk-button custom-button-1">確認</a>
                                                            </p>
                                                        </div>
                                                        <a href="#confirmAutoBid" id="confirmAutoBidButton" class="uk-button custom-button-1">設定</a>

                                                    @endauth
                                                    @guest
                                                        <a class="uk-button custom-button-1" href="#auto-bid-login-notice" uk-toggle>
                                                            設定
                                                        </a>
                                                        <div id="auto-bid-login-notice" class="uk-flex-top" uk-modal>
                                                            <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                                                <h2 class="uk-modal-title">自動出價前需要先登入</h2>
                                                                <div class="uk-flex uk-flex-right">
                                                                    <a class="uk-button custom-button-1"
                                                                       href="{{ route('login.show', ['redirectUrl'=>'lots_'.$lot->id]) }}">登入</a>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    @endguest
                                                </div>
                                            </div>
                                            @auth
                                                <div id="my-auto-bid-section" {{ Auth::user()->getLotAutoBid($lot->id) === null? "hidden" : "" }}>
                                                    <label style="font-size: 0.9em">您的自動出價 - NT$
                                                        <span id="my-auto-bid">{{ number_format(optional(Auth::user()->getLotAutoBid($lot->id))->bid) ?? '' }}</span></label>
                                                </div>
                                            @endauth
                                        </div>
                                        <div class="uk-overflow-auto" style="max-height: 30vh;">
                                            <table class="uk-table">
                                                <tbody id="bidHistories" style="font-size: 0.8em">
                                                @foreach($lot->bidRecords as $bidRecord)
                                                    <tr>
                                                        @auth
                                                            @if($bidRecord->bidder_id === Auth::user()->id)
                                                                <td>你</td>
                                                            @else
                                                                <td class="uk-text-nowrap">競標者 {{ $bidRecord->bidder_alias }}</td>
                                                            @endif
                                                        @endauth
                                                        @guest
                                                            <td class="uk-text-nowrap">競標者 {{ $bidRecord->bidder_alias }}</td>
                                                        @endguest


                                                        <td style="font-size: 0.8em">{{ $bidRecord->created_at }}</td>
                                                        <td>NT${{ number_format($bidRecord->bid) }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <hr>
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
                            <div style="box-sizing: border-box; border-right: 1em #fff solid; border-left: 1em #fff solid">
                                <p>
                                    {!! nl2br($lot->description) !!}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <input id="bidderId" value="{{ optional(Auth::user())->id }}" hidden>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
    <style>
        .uk-active > a {
            border-bottom: 2px solid #003a6c !important;
        }
    </style>
    <style>
        .lot-head {
            font-size: 2.5em;
        }

        @media (min-width: 1200px) {

            .lot-head {
                font-size: 3.5em;
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
            constructor (date) {
                let { userAgent } = window.navigator;
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
                        '<p style="color: #666">'+success.data.text+'</p>',
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

        let countdown;
        let dueTime;
        let dueDo;
        let setAuctionCountdown = function(){
            const second = 1000,
                minute = second * 60,
                hour = minute * 60,
                day = hour * 24;

            function freshCountdown(dueTime, dueDo){
                let now = new Date().getTime();

                let distance = dueTime - now;

                //do something later when date is reached
                if (distance < 1000) {
                    clearInterval(countdown);
                    dueDo();
                } else {
                    $(".countdown-days").text(Math.floor(distance / (day)).toString().padStart(2, '0'));
                    $(".countdown-hours").text(Math.floor((distance % (day)) / (hour)).toString().padStart(2, '0'));
                    $(".countdown-minutes").text(Math.floor((distance % (hour)) / (minute)).toString().padStart(2, '0'));
                    $(".countdown-seconds").text(Math.floor((distance % (minute)) / second).toString().padStart(2, '0'));
                }
            }
            function nothing() {}

            function auctionEnd()
            {
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: '競標結束',
                    showConfirmButton: false,
                    timer: 1500
                })
                dueDo = nothing;
                countdown.prop('hidden', true);
                $('#auction-end-title').prop('hidden', false);
            }

            function auctionStart()
            {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: '競標開始',
                    showConfirmButton: false,
                    timer: 1500
                })

                let datetime = new Date(countdown.attr('auction-end-at'));
                countdown.attr('end-at', datetime.toISOString());

                dueTime = new Date(datetime).getTime();
                dueDo = auctionEnd;
                $("#auction-countdown-action").text('後結束競標');
            }

            //init
            let auctionStatus = $('#auction-status').val();
            if(auctionStatus === "0") {
                dueDo = auctionStart;
            } else if (auctionStatus === "1") {
                dueDo = auctionEnd;
            } else {
                dueDo = nothing;
            }

            countdown = $('.countdown');

            setInterval(function() {
                let dueTimeIso = countdown.attr('end-at');
                dueTime = new Date(dueTimeIso).getTime();
                freshCountdown(dueTime, dueDo)
            }, 250)
        };
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

                $('#bidHistories').prepend(newBid);
                $('#currentBid').text(number_format(e.bid));
                $('#nextBid').text(number_format(parseInt(e.bid) + bidRule(parseInt(e.bid))));
                let auctionEndAt = $('.countdown');
                if (e.auction_end_at !== auctionEndAt.attr('auction-end-at')) {
                    auctionEndAt.attr('end-at', e.auction_end_at);
                    let datetime = new Date(e.auction_end_at);
                    dueTime = datetime.toISOString();
                    countdown.attr('end-at', datetime.toISOString());

                    //let datetime = new Date(Date.parse(e.auction_end_at.replace(/-/g, '/')));//fix safari


                    /////////延長時間

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

            setAuctionCountdown();

            $("#favorite").click(function () {
                addFavorite({{ $lot->id }});
            });

            $("#confirmManualBidButton").click(function(event) {
                $(this).modal();
                let bidInput = $('#bidInput').val();
                $('#confirmManualBidTitle').text('確認出價 NT$'+number_format(bidInput));
                return false;
            });

            $("#confirmAutoBidButton").click(function(event) {
                $(this).modal();
                let bidInput = $('#autoBidInput').val();
                $('#confirmAutoBidTitle').text('確認設定自動出價 NT$'+number_format(bidInput));
                return false;
            });

            $("#bid").click(function () {
                let bidInput = $('#bidInput')
                bid({{ $lot->id }}, bidInput.val());
                bidInput.val('');
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
@endpush
