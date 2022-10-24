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
            <div class="uk-margin uk-child-width-1-4@s uk-text-center" uk-grid>
                @foreach($sCategories as $sCategory)
                    <div>
                        <div class="uk-card uk-card-default uk-card-small uk-card-hover uk-card-body" style="color: #fff; background-color: {{ $mCategory->color_hex }};">
                            {{ $sCategory->name }}
                            <a href="{{ route('mart.s_categories.show', [$mCategory, $sCategory]) }}" class="uk-position-cover"></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
