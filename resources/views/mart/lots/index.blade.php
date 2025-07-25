@extends('layouts.member')
@inject('carbonPresenter', 'App\Presenters\CarbonPresenter')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>

            <!-- 拍賣商品區塊 -->
            @if($lots->where('auction_end_at', '!=', null)->count() > 0)
                <h3 class="uk-card-title">拍賣商品</h3>
                <div class="uk-child-width-1-3@s uk-child-width-1-4@m uk-grid-small uk-grid-match" uk-grid>
                    @foreach($lots->where('auction_end_at', '!=', null) as $singleLot)
                        <div>
                            <div class="uk-card uk-card-default uk-card-hover bidding-card-click" lotId="{{ $singleLot->id }}">
                                <div class="uk-card-media-top">
                                    <div class="uk-background-cover uk-height-medium uk-panel uk-flex uk-flex-center uk-flex-middle"
                                         style="background-image: url({{ $singleLot->blImages->first()->url }});">
                                    </div>
                                </div>
                                <div class="uk-card-body">

                                    <div class="uk-flex uk-flex-right">
                                        @include('mart.components.favorite-inline', $singleLot)
                                    </div>
                                    <h3 class="uk-card-title uk-text-truncate custom-font-medium">{{ $singleLot->name }}</h3>
                                    <label class="custom-font-medium" id="lot-{{ $singleLot->id }}-price" style="color: #003a6c">NT${{ number_format($singleLot->current_bid) }}</label>
                                    <p>{!! $carbonPresenter->lotPresent($singleLot->id, $singleLot->auction_end_at) !!}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- 商店直賣商品區塊 -->
            @if($lots->where('auction_end_at', null)->count() > 0)
                <h3 class="uk-card-title">Antiquary 精選</h3>
                <div class="uk-child-width-1-3@s uk-child-width-1-4@m uk-grid-small uk-grid-match" uk-grid>
                    @foreach($lots->where('auction_end_at', null) as $singleLot)
                        <div>
                            <div class="uk-card uk-card-default uk-card-hover antiquary-card-click" lotId="{{ $singleLot->id }}">
                                <div class="uk-card-media-top">
                                    <div class="uk-background-cover uk-height-medium uk-panel uk-flex uk-flex-center uk-flex-middle"
                                         style="background-image: url({{ $singleLot->blImages->first()->url }});">
                                    </div>
                                </div>
                                <div class="uk-card-body">

                                    <div class="uk-flex uk-flex-right">
                                        @include('mart.components.favorite-inline', $singleLot)
                                    </div>
                                    <h3 class="uk-card-title custom-font-medium">{{ $singleLot->name }}</h3>
                                    <label class="custom-font-medium" id="lot-{{ $singleLot->id }}-price" style="color: #003a6c">NT${{ number_format($singleLot->current_bid) }}</label>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @include('mart.components.favorite-outline')
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}"
          crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
        $(function () {
            $(".bidding-card-click").click(function(e){
                // 如果點擊的是愛心元素或其子元素，不執行卡片點擊事件
                if ($(e.target).closest('.favorite, .un-login-favorite').length > 0) {
                    return;
                }

                let lotId = $(this).attr('lotId');
                let url = '{{ route("mart.lots.show", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
            });
        });
        $(function () {
            $(".antiquary-card-click").click(function(e){
                // 如果點擊的是愛心元素或其子元素，不執行卡片點擊事件
                if ($(e.target).closest('.favorite, .un-login-favorite').length > 0) {
                    return;
                }

                let lotId = $(this).attr('lotId');
                let url = '{{ route("mart.products.show", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
            });
        });
    </script>

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

        Echo.channel(`lotCard`)
            .listen('FreshLotCardPrice', (e) => {
                let lotPrice = $('#lot-'+e.lotId+'-price');
                let bid = e.bid;
                lotPrice.text('NT$'+number_format(bid));
            });
        Echo.channel(`lotCard`)
            .listen('FreshLotCardTime', (e) => {
                let countdown = $('#countdown-'+e.lotId);
                countdown.attr('end-at', e.dueTime);
            });

        let setLotCardCountdown = function(countdown){
            const second = 1000,
                minute = second * 60,
                hour = minute * 60,
                day = hour * 24;

            function freshCountdown(countdown, dueTime){
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
                    if(distance > 86400000) {
                        countdown.text('於 '+days+ '天內結束競標')
                    } else {
                        countdown.text('於 '+hours+ '時'+minutes+'分'+seconds+'秒 後結束')
                    }
                }
            }

            let timer = setInterval(function() {
                let dueTimeIso = countdown.attr('end-at');
                let dueTime = new Date(dueTimeIso).getTime();
                freshCountdown(countdown, dueTime)
            }, 500)
        };

        $(function () {
            lotCardCountdowns = $('.lot-card-countdowns');

            lotCardCountdowns.each(function () {
                let lotCardCountdown = $('#'+this.id);
                setLotCardCountdown(lotCardCountdown);
            });
        });
    </script>
@endpush
