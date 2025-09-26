@extends('layouts.auctioneer')

@section('content')
@if (session('notification'))
    <div class="uk-alert-success" uk-alert>
        <a class="uk-alert-close" uk-close></a>
        <p>{{ session('notification') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="uk-alert-danger" uk-alert>
        <a class="uk-alert-close" uk-close></a>
        <p>{{ session('error') }}</p>
    </div>
@endif

<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">{{ $head }}</h1>
    <div class="uk-margin">
        <a href="{{ route('auctioneer.articles.create') }}" class="uk-button uk-button-primary">
            <span uk-icon="plus"></span> 新增文章
        </a>
    </div>
</div>

<div class="uk-margin">
    @if($articles->count() > 0)
        <div class="uk-child-width-1-1" uk-grid>
            @foreach($articles as $article)
                <div>
                    <div class="uk-card uk-card-default uk-card-hover">
                        <div class="uk-card-header">
                            <div class="uk-grid-small uk-flex-middle" uk-grid>
                                <div class="uk-width-expand">
                                    <h3 class="uk-card-title uk-margin-remove-bottom">{{ $article->title }}</h3>
                                    @if($article->subtitle)
                                        <p class="uk-text-meta uk-margin-remove-top">{{ $article->subtitle }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="uk-card-body">
                            <p>{{ Str::limit($article->intro, 150) }}</p>
                            <p class="uk-text-meta">
                                <span uk-icon="icon: user; ratio: 0.8"></span> {{ $article->auctioneer->name }}
                                <span uk-icon="icon: clock; ratio: 0.8" style="margin-left: 20px;"></span> {{ $article->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                        <div class="uk-card-footer">
                            <div class="uk-grid-small uk-flex-middle" uk-grid>
                                <div class="uk-width-auto">
                                    <a href="{{ route('auctioneer.articles.edit', $article->id) }}" class="uk-button uk-button-small uk-button-primary">
                                        編輯
                                    </a>
                                </div>
                                <div class="uk-width-auto">
                                    <button class="uk-button uk-button-small uk-button-danger" 
                                            onclick="confirmDeleteArticle({{ $article->id }}, '{{ $article->title }}')">
                                        刪除
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="uk-card uk-card-default">
            <div class="uk-card-body uk-text-center">
                <p class="uk-text-muted">尚未有文章內容</p>
                <a href="{{ route('auctioneer.articles.create') }}" class="uk-button uk-button-primary">創建第一篇文章</a>
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteArticleModal" class="modal">
    <div class="modal-content">
        <h3>確認刪除</h3>
        <p id="deleteArticleMessage"></p>
        <div class="modal-buttons">
            <button class="uk-button uk-button-default" onclick="$.modal.close()">取消</button>
            <button class="uk-button uk-button-danger" id="confirmDeleteArticle">刪除</button>
        </div>
    </div>
</div>

<script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}">

<script>
let currentDeleteArticleId = null;

function confirmDeleteArticle(articleId, articleTitle) {
    currentDeleteArticleId = articleId;
    document.getElementById('deleteArticleMessage').textContent = `您確定要刪除文章「${articleTitle}」嗎？`;
    $('#deleteArticleModal').modal();
}

document.getElementById('confirmDeleteArticle').addEventListener('click', function() {
    if (currentDeleteArticleId) {
        // 創建表單並提交
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('auctioneer/dashboard/articles') }}/${currentDeleteArticleId}`;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        const tokenField = document.createElement('input');
        tokenField.type = 'hidden';
        tokenField.name = '_token';
        tokenField.value = '{{ csrf_token() }}';

        form.appendChild(methodField);
        form.appendChild(tokenField);
        document.body.appendChild(form);
        form.submit();
    }
});
</script>
@endsection
