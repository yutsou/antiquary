@extends('layouts.expert')

@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <a href="{{ route('expert.sub_categories.create', $mainCategoryId) }}" class="uk-button custom-button-1">創建子分類</a>
    </div>
    <div class="uk-margin-medium">
        <div class="uk-grid-column-small uk-grid-row-large uk-child-width-1-5@s uk-text-center" uk-grid>
            @foreach ($subCategories as $subCategory)
                <div>
                    <a href="{{ route('expert.sub_categories.edit', [$mainCategoryId, $subCategory->id]) }}" class="custom-link-mute">
                        <div class="uk-card uk-card-default uk-card-body uk-card-small">{{ $subCategory->name }}</div>
                    </a>

                </div>
            @endforeach
        </div>
    </div>
@endsection
