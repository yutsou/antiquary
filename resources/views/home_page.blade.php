@extends('layouts.member')

@inject('carbonPresenter', 'App\Presenters\CarbonPresenter')
@push('scripts')
    <script>
        // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
        let vh = window.innerHeight * 0.01;
        // Then we set the value in the --vh custom property to the root of the document
        document.documentElement.style.setProperty('--vh', `${vh}px`);

        // We listen to the resize event
        window.addEventListener('resize', () => {
            // We execute the same script as before
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        });
    </script>
@endpush
@section('sub-content')
    <div class="uk-position-relative uk-visible-toggle uk-light uk-visible@l" tabindex="-1" uk-slideshow="autoplay: true; animation: slide">
        <ul class="uk-slideshow-items" uk-height-viewport>
            @if($banners->count() != 0)
                @foreach($banners as $banner)
                    <li>
                        <a href="{{ $banner->link }}">
                            <img src="{{ $banner->desktop_banner->url }}" alt="{{ $banner->slogan }}" uk-cover>
                            @if($banner->slogan != '')
                                <div class="uk-position-center uk-position-medium uk-text-center uk-light">
                                    <h2 class="uk-margin-remove">{{ $banner->slogan }}</h2>
                                    <hr class="hr">
                                </div>
                            @endif
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
        <a class="uk-position-center-left uk-position-small uk-hidden-hover" uk-slidenav-previous
           uk-slideshow-item="previous"></a>
        <a class="uk-position-center-right uk-position-small uk-hidden-hover" uk-slidenav-next
           uk-slideshow-item="next"></a>
    </div>

    <div class="uk-position-relative uk-visible-toggle uk-light uk-hidden@l" tabindex="-1" uk-slideshow="autoplay: false; animation: slide;">
        <ul class="uk-slideshow-items my-element" >
            @foreach($banners as $banner)
                <li>
                    <a href="{{ $banner->link }}">
                        <img src="{{ $banner->mobile_banner->url }}" alt="{{ $banner->slogan }}" uk-cover>
                        <div class="uk-position-bottom uk-position-medium uk-text-center uk-light">
                            <h3 class="uk-margin-remove">{{ $banner->slogan }}</h3>
                            <hr class="hr">
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
        <a class="uk-position-center-left uk-position-small uk-hidden-hover" uk-slidenav-previous
           uk-slideshow-item="previous"></a>
        <a class="uk-position-center-right uk-position-small uk-hidden-hover" uk-slidenav-next
           uk-slideshow-item="next"></a>
    </div>
@endsection

@section('content')
    @if($auctions->where('status', 0)->count() != 0)
        <h3 class="uk-card-title">準備開始的拍賣會</h3>
        <div class="uk-visible@m">
            <div class="uk-slider-container-offset" uk-slider="finite: true">
                <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1">
                    <ul class="uk-slider-items uk-child-width-1-4@s uk-grid">
                        @foreach($auctions->where('status', 0) as $auction)
                            <li>
                                <div class="uk-card uk-card-default uk-card-hover custom-card-click" auctionId="{{ $auction->id }}">
                                    <div class="uk-card-media-top">
                                        <img src="{{ $auction->lots->first()->images->first()->url }}" alt="" style="width: 100vw; height: 300px; object-fit: cover;">
                                    </div>
                                    <div class="uk-card-body">
                                        <h3 class="uk-card-title">{{ $auction->name }}</h3>
                                        <p>{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->end_at) }}</p>
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
                                        <p>{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->end_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @if($auctions->where('status', 1)->count() != 0)
        <h3 class="uk-card-title">進行中的拍賣會</h3>
        <div class="uk-visible@m">
            <div class="uk-slider-container-offset" uk-slider="finite: true">
                <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1">
                    <ul class="uk-slider-items uk-child-width-1-4@s uk-grid">
                        @foreach($auctions->where('status', 1) as $auction)
                            <li>
                                <div class="uk-card uk-card-default uk-card-hover custom-card-click" auctionId="{{ $auction->id }}">
                                    <div class="uk-card-media-top">
                                        <img src="{{ $auction->lots->first()->images->first()->url }}" alt="" style="width: 100vw; height: 300px; object-fit: cover;">
                                    </div>
                                    <div class="uk-card-body">
                                        <h3 class="uk-card-title">{{ $auction->name }}</h3>
                                        <p>{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->end_at) }}</p>
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
                                        <p>{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->end_at) }}</p>
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
