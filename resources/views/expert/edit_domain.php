@extends('layouts.expert')

@section('content')
<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">{{ $head }}</h1>
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
<form class="uk-form-stacked" method="POST" action="{{ route('domain.update', ['domainId'=>$domain->id]) }}" enctype="multipart/form-data">
    @csrf
    <div class="uk-margin">
        <label class="uk-form-label" for="brief">給這個主分類一個簡短的描述</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon:  info"></span>
            <input type="text" class="uk-input uk-form-width-large" id="brief" name="brief" value="{{ $domain->brief }}">
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="introduction">給這個主分類詳細的介紹自己的專長</label>
        <div>
            <textarea class="uk-textarea" id="introduction" name="introduction" rows="5">{{ $domain->introduction }}</textarea>
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="year">描述你從事相關工作或收藏的年資</label>
        <div class="uk-margin">
            <div class="uk-inline uk-form-controls">
                <span class="uk-form-icon" uk-icon="icon:  history"></span>
                <input type="text" class="uk-input uk-form-width-large" id="year" name="year" value="{{ $domain->year }}" ><br>
            </div>
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="experience">描述你相關的經歷</label>
        <div class="uk-margin">
            <div class="uk-inline uk-form-controls">
                <span class="uk-form-icon" uk-icon="icon:  history"></span>
                <input type="text" class="uk-input uk-form-width-large" id="experience" name="experience" value="{{ $domain->experience }}" ><br>
            </div>
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="scale">描述你經手或收藏的規模</label>
        <div class="uk-margin">
            <div class="uk-inline uk-form-controls">
                <span class="uk-form-icon" uk-icon="icon:  info"></span>
                <input type="text" class="uk-input uk-form-width-large" id="scale" name="scale" value="{{ $domain->scale }}" ><br>
            </div>
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="name">專家圖片</label>
        <div uk-form-custom>
            <input type="file" accept="image/*" onchange="loadFile(event)" name="image">
            <button class="uk-button uk-button-default" type="button" tabindex="-1">上傳圖片</button>
        </div>
        <figure class="uk-box-shadow-medium" style="background-image: url('/images/web/common/no-picture.jpg'); height:200px; width:200px">
            <img src="{{ optional($domain->image)->url }}" class="image-no-known" id="output" uk-img width="200px" height="200px">
        </figure>
    </div>

    <div class="uk-margin">
        <button type="submit" class="uk-button custom-color-group-1">建立</button>
    </div>
</form>
@endsection
