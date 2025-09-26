@extends('layouts.auctioneer')

@section('content')
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

<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">{{ $head }}</h1>
</div>

@if ($errors->any())
    <div class="uk-alert-danger" uk-alert>
        <a class="uk-alert-close" uk-close></a>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="uk-form-stacked" method="POST" action="{{ route('auctioneer.articles.update', $article->id) }}">
    @csrf
    @method('POST')
    <div class="uk-margin">
        <label class="uk-form-label" for="title">文章標題</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: file-text"></span>
            <input type="text" 
                   class="uk-input uk-form-width-large @error('title') uk-form-danger @enderror" 
                   id="title" 
                   name="title" 
                   value="{{ old('title', $article->title) }}" 
                   placeholder="請輸入文章標題"
                   required>
        </div>
        @error('title')
            <div class="uk-text-danger uk-margin-small-top">{{ $message }}</div>
        @enderror
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="subtitle">副標題 (選填)</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: tag"></span>
            <input type="text" 
                   class="uk-input uk-form-width-large @error('subtitle') uk-form-danger @enderror" 
                   id="subtitle" 
                   name="subtitle" 
                   value="{{ old('subtitle', $article->subtitle) }}" 
                   placeholder="請輸入副標題 (選填)">
        </div>
        @error('subtitle')
            <div class="uk-text-danger uk-margin-small-top">{{ $message }}</div>
        @enderror
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="intro">文章簡介</label>
        <div class="uk-form-controls">
            <textarea class="uk-textarea uk-form-width-large @error('intro') uk-form-danger @enderror" 
                      id="intro" 
                      name="intro" 
                      rows="4" 
                      placeholder="請輸入文章簡介" 
                      required>{{ old('intro', $article->intro) }}</textarea>
        </div>
        @error('intro')
            <div class="uk-text-danger uk-margin-small-top">{{ $message }}</div>
        @enderror
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="content">文章內容</label>
        <div class="uk-form-controls">
            <textarea class="uk-textarea uk-form-width-large @error('content') uk-form-danger @enderror" 
                      id="content" 
                      name="content" 
                      rows="15" 
                      placeholder="請輸入文章內容" 
                      required>{{ old('content', $article->content) }}</textarea>
        </div>
        @error('content')
            <div class="uk-text-danger uk-margin-small-top">{{ $message }}</div>
        @enderror
    </div>

    <div class="uk-margin">
        <button type="submit" class="uk-button uk-button-primary uk-margin-right">
            <span uk-icon="check"></span> 更新文章
        </button>
        <a href="{{ route('auctioneer.articles.index') }}" class="uk-button uk-button-default">
            <span uk-icon="arrow-left"></span> 返回列表
        </a>
    </div>
</form>

<script>
    // 可以添加一些實時的字符計數或其他JavaScript功能
    $(document).ready(function() {
        // 文章內容字數計數
        $('#content').on('input', function() {
            var content = $(this).val();
            var words = content.length;
            $(this).siblings('.uk-text-meta').remove();
            $(this).after('<div class="uk-text-meta">字數：' + words + ' 字</div>');
        });
        
        $('#content').trigger('input');
    });
</script>
@endsection
