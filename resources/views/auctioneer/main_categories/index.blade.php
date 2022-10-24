@extends('layouts.auctioneer')

@section('content')
<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">{{ $head }}</h1>
</div>
<div class="uk-grid-column-small uk-grid-row-large uk-child-width-1-4@s uk-text-center" uk-grid>
    @foreach ($categoryRoots as $mainCategory)
    <div>
        <a href="{{ route('auctioneer.main_categories.edit', ['mainCategoryId'=>$mainCategory->id]) }}" class="boxhead">
            <div class="uk-card uk-card-default custom-image-horver" style="background-color: {{ $mainCategory->color_hex }}">
                <div class="uk-card-media-top">
                    <img src="{{ $mainCategory->image->url }}" alt="">
                </div>
                <div class="uk-card-body">
                    <h3 class="uk-card-title" style="color: white;">{{ $mainCategory->name }}</h3>
                </div>
            </div>
        </a>

    </div>
    @endforeach
</div>
@endsection
