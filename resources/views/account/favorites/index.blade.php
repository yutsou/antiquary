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
            @foreach($favorites as $lot)
                <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                    <div class="uk-card-media-left uk-cover-container uk-width-1-3 uk-width-1-5@m">
                        <img src="{{ $lot->images->first()->url }}" alt="" uk-cover>
                    </div>
                    <div class="uk-width-expand">
                        <div class="uk-card-body" style="padding: 20px 20px">
                            <div class="uk-margin uk-text-right">
                                <p>{{ $carbonPresenter->lotPresent($lot->auction_start_at, $lot->auction_end_at) }}</p>
                            </div>
                            <hr>
                            <h3 class="uk-card-title uk-text-truncate" style="margin: 0 0 0 0">ID.{{ $lot->id }}
                                - {{ $lot->name }}</h3>
                            <div class="uk-margin uk-flex uk-flex-right">
                                <a href="{{ route('mart.lots.show', ['lotId'=>$lot->id]) }}"
                                   class="uk-button custom-button-1">前往競標</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
