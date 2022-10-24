@extends('layouts.member')
@inject('carbonPresenter', 'App\Presenters\CarbonPresenter')
@section('sub-content')
    <div class="uk-flex uk-flex-center uk-flex-middle uk-background-cover uk-light"
         data-src="/images/web/home_page/banner/banner-1.jpg" uk-img style="height: 720px;">
        <h1>拍賣都交由專家管理</h1>
    </div>
@endsection

@section('content')
    @if(count($auctions) != 0)
        <h3 class="uk-card-title">準備開始的拍賣會</h3>
        <div class="uk-visible@m">
            <div class="uk-slider-container-offset" uk-slider>
                <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1">
                    <ul class="uk-slider-items uk-child-width-1-4@s uk-grid">
                        @foreach($auctions as $auction)
                            <li>
                                <div class="uk-card uk-card-default uk-card-hover custom-card-click" auctionId="{{ $auction->id }}">
                                    <div class="uk-card-media-top">
                                        <img src="{{ $auction->lots->first()->images->first()->url }}" alt="" style="width: 100vw; height: 300px; object-fit: cover;">
                                    </div>
                                    <div class="uk-card-body">
                                        <h3 class="uk-card-title">{{ $auction->name }}</h3>
                                        <p>{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->expect_end_at) }}</p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous
                       uk-slider-item="previous"></a>
                    <a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next
                       uk-slider-item="next"></a>
                </div>
                <ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin"></ul>
            </div>
        </div>
        <div class="uk-hidden@m">
            <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1" uk-slider="center: true">
                <ul class="uk-slider-items uk-grid uk-grid-small uk-grid-match" uk-height-viewport="offset-top: true; offset-bottom: 30">
                    @foreach($auctions as $auction)
                        <li class="uk-width-5-6">
                            <div >
                                <div class="uk-card uk-card-default uk-card-hover custom-card-click" auctionId="{{ $auction->id }}">
                                    <div class="uk-card-media-top">
                                        <img src="{{ $auction->lots->first()->images->first()->url }}" alt="" style="width: 100vw; height: 300px; object-fit: cover;">
                                    </div>
                                    <div class="uk-card-body">
                                        <h3 class="uk-card-title">{{ $auction->name }}</h3>
                                        <p>{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->expect_end_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
    <script>
        $(function () {
            $('.custom-card-click').on('click', function() {
                let aucitonId = $(this).attr('auctionId');
                window.location.assign('/auctions/'+aucitonId);
            });
        });
    </script>
@endpush
