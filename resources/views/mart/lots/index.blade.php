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
            <div class="uk-child-width-1-3@s uk-child-width-1-4@m uk-grid-small uk-grid-match" uk-grid>
                @foreach($lots as $lot)
                    <div>
                        <div class="uk-card uk-card-default uk-card-hover custom-card-click" lotId="{{ $lot->id }}">
                            <div class="uk-card-media-top">
                                <div class="uk-background-cover uk-height-medium uk-panel uk-flex uk-flex-center uk-flex-middle"
                                     style="background-image: url({{ $lot->images->first()->url }});">
                                </div>
                            </div>
                            <div class="uk-card-body">

                                <div class="uk-flex uk-flex-right">
                                    @include('mart.components.favorite-inline', $lot)
                                </div>
                                <h3 class="uk-card-title uk-text-truncate custom-font-medium">{{ $lot->name }}</h3>
                                <label class="custom-font-medium" style="color: #003a6c">NT${{ number_format($lot->current_bid) }}</label>
                                <p>{{ $carbonPresenter->lotPresent($lot->auction_start_at, $lot->auction_end_at) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @include('mart.components.favorite-outline')
@endsection
@push('scripts')
    <script>
        $(function () {
            $(".custom-card-click").click(function(){
                let lotId = $(this).attr('lotId');
                let url = '{{ route("mart.lots.show", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
            });
        });
    </script>
@endpush
