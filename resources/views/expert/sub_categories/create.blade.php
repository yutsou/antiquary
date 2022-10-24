@extends('layouts.expert')

@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <a href="{{ route('expert.sub_categories.index', $mainCategoryId) }}" class="custom-link"> > 返回子分類管理</a>
    </div>
    @if (session('notification'))
        <script>
            $(function () {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: '{{ session('notification') }}',
                    showConfirmButton: false,
                    timer: 1500
                })
            });
        </script>
    @endif
    <form class="uk-form-stacked" method="POST" action="{{ route('expert.sub_categories.store', ['mainCategoryId'=>$mainCategoryId]) }}" enctype="multipart/form-data">
        @csrf
        <div class="uk-margin">
            <label class="uk-form-label" for="name">子分類名稱</label>
            <div class="uk-inline uk-form-controls">
                <span class="uk-form-icon" uk-icon="icon: thumbnails"></span>
                <input type="text" class="uk-input uk-form-width-large" id="name" name="name" value="{{ old('name') }}" required>
            </div>
        </div>
        <div class="uk-margin">
            <label class="uk-form-label" for="name">子分類英文名稱</label>
            <div class="uk-inline uk-form-controls">
                <span class="uk-form-icon" uk-icon="icon: thumbnails"></span>
                <input type="text" class="uk-input uk-form-width-large" id="urlName" name="url_name" value="{{ old('url_name') }}" required>
            </div>
        </div>
        <div class="uk-margin">
            <button type="submit" class="uk-button custom-color-group-1">建立</button>
        </div>
    </form>
@endsection

