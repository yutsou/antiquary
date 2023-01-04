@extends('layouts.member')

@section('content')
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
    <a id="test">Test</a>
@endsection
@push('scripts')
    <script>
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
        $(function () {
            setAuctionCountdown();

            $('#test').click(function () {
                let datetime = new Date('2022-11-23 06:53:00');
                dueTime = datetime.toISOString();
                //let datetime = new Date(Date.parse(e.auction_end_at.replace(/-/g, '/')));//fix safari
                countdown.attr('end-at', datetime.toISOString());
            });
        });
    </script>
@endpush
