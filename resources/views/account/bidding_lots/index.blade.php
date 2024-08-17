@extends('layouts.member')
@inject('carbonPresenter', 'App\Presenters\CarbonPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>

    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            <div>
                @foreach($lots as $singleLot)
                    <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                        <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                            <img src="{{ $singleLot->blImages->first()->url }}" alt="" uk-cover>
                        </div>
                        <div class="uk-width-expand">
                            <div class="uk-card-body" style="padding: 20px 20px">
                                <div class="uk-margin uk-text-right">
                                    <p>{!! $carbonPresenter->lotPresent($singleLot->id, $singleLot->auction_end_at) !!}</p>
                                </div>
                                <hr>
                                <h3 class="uk-card-title" style="margin: 0 0 0 0">{{ $singleLot->name }}</h3>
                                <hr>
                                <div class="uk-margin uk-text-right">
                                    <a href="{{ route('mart.lots.show', ['lotId'=>$singleLot->id]) }}"
                                       class="uk-button custom-button-1">前往競標</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
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
