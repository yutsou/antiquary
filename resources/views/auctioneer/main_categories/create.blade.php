@extends('layouts.auctioneer')

@section('content')
<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">管理員中心</h1>
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
<script>
    var loadFile = function(event) {
      var output = document.getElementById('output');
      output.src = URL.createObjectURL(event.target.files[0]);
      output.onload = function() {
        URL.revokeObjectURL(output.src) // free memory
      }
    };
</script>
<form class="uk-form-stacked" method="POST" action="{{ route('auctioneer.main_categories.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="uk-margin">
        <label class="uk-form-label" for="name">主分類名稱</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: thumbnails"></span>
            <input type="text" class="uk-input uk-form-width-large" id="name" name="name" value="{{ old('name') }}" required>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="name">主分類英文名稱</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: thumbnails"></span>
            <input type="text" class="uk-input uk-form-width-large" id="urlName" name="url_name" value="{{ old('url_name') }}" required>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="name">主分類顏色</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: paint-bucket"></span>
            <input type="color" class="uk-input uk-form-width-large" id="colorHex" name="color_hex" value="#003a6c" required>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="name">主分類圖片</label>
        <div uk-form-custom>
            <input type="file" accept="image/*" onchange="loadFile(event)" name="image" required>
            <button class="uk-button uk-button-default" type="button" tabindex="-1">上傳圖片</button>
        </div>
        <figure class="uk-box-shadow-medium" style="background-image: url({{asset('/images/web/common/no-picture.jpg')}}); height:200px; width:200px">
            <img class="image-no-known" id="output" uk-img width="200px" height="200px">
        </figure>

    </div>
    <div class="uk-margin">
        <button type="submit" class="uk-button custom-button-1">建立</button>
    </div>
</form>
@endsection
