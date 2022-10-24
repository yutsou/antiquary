@extends('layouts.auctioneer')

@section('content')
<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">管理員中心</h1>
</div>
@if (session('notification'))
    <script>
        $(document).ready(function()
        {
            UIkit.notification({
                message: '<div class="uk-text-center"><span uk-icon=\'icon: check\'></span>{{ session('notification') }}</div>',
                status: 'success',
                pos: 'top-center',
                timeout: 2000
            });
        });
    </script>
@endif
<script>
    var loadFile = function(event) {
      var output = document.getElementById('output');
      output.src = URL.createObjectURL(event.target.files[0]);
      output.onload = function() {
        URL.revokeObjectURL(output.src) // free memory
      }
    };
</script>
<form class="uk-form-stacked" method="POST" action="{{ route('auctioneer.main_categories.update', ['mainCategoryId'=>$category->id]) }}" enctype="multipart/form-data">
    @csrf
    <div class="uk-margin">
        <label class="uk-form-label" for="name">主分類名稱</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: thumbnails"></span>
            <input type="text" class="uk-input uk-form-width-large" id="name" name="name" value="{{ $category->name }}" required>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="name">主分類英文名稱</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: thumbnails"></span>
            <input type="text" class="uk-input uk-form-width-large" id="urlName" name="url_name" value="{{ $category->url_name }}" required>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="name">主分類顏色</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: paint-bucket"></span>
            <input type="text" class="uk-input uk-form-width-large" id="colorHex" name="color_hex" value="{{ $category->color_hex }}" required placeholder="#ffffff">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="name">主分類圖片</label>
        <div uk-form-custom>
            <input type="file" accept="image/*" onchange="loadFile(event)" name="image">
            <button class="uk-button uk-button-default" type="button" tabindex="-1">上傳圖片</button>
        </div>
        <figure class="uk-box-shadow-medium" style="background-image: url('/images/web/common/no-picture.jpg'); height:200px; width:200px">
            <img src="{{ optional($category->image)->url }}" class="image-no-known" id="output" uk-img width="200px" height="200px">
        </figure>
    </div>
    <div class="uk-margin">
        <button type="submit" class="uk-button custom-color-group-1">修改</button>
    </div>
</form>
@endsection
