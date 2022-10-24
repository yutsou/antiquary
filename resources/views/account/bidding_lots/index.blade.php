@extends('layouts.member')

@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    @foreach($lots as $lot)
        <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
            <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                <img src="{{ $lot->images->first()->url }}" alt="" uk-cover>
            </div>
            <div class="uk-width-expand">
                <div class="uk-card-body" style="padding: 20px 20px">
                    <div class="uk-margin uk-text-right">
                        <label>
                            於 {{ $lot->auction->auction_start_at }} 開始競標
                        </label>
                    </div>
                    <hr>
                    <h3 class="uk-card-title" style="margin: 0 0 0 0">ID.{{ $lot->id }}
                        - {{ $lot->name }}</h3>
                    <div class="uk-margin uk-text-right">
                        <a href="{{ route('mart.lots.show', ['lotId'=>$lot->id]) }}"
                           class="uk-button custom-button-1">前往競標</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
