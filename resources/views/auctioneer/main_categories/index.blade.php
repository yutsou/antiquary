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
</div>
<div class="uk-grid-column-small uk-grid-row-large uk-child-width-1-4@s uk-text-center" uk-grid>
    @foreach ($categoryRoots as $mainCategory)
    <div>
        <div class="uk-card uk-card-default custom-image-horver" style="background-color: {{ $mainCategory->color_hex }}">
            <div class="uk-card-media-top">
                <img src="{{ $mainCategory->image->url }}" alt="">
            </div>
            <div class="uk-card-body">
                <h3 class="uk-card-title" style="color: white;">{{ $mainCategory->name }}</h3>
                <div class="uk-margin-small-top">
                    <a href="{{ route('auctioneer.main_categories.edit', ['mainCategoryId'=>$mainCategory->id]) }}"
                       class="uk-button uk-button-small uk-button-primary" style="margin-right: 5px;">編輯</a>
                    <button class="uk-button uk-button-small uk-button-danger"
                            onclick="confirmDeleteMainCategory({{ $mainCategory->id }}, '{{ $mainCategory->name }}')">刪除</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteMainCategoryModal" class="modal">
    <div class="modal-content">
        <h3>確認刪除</h3>
        <p id="deleteMainCategoryMessage"></p>
        <div class="modal-buttons">
            <button class="uk-button uk-button-default" onclick="$.modal.close()">取消</button>
            <button class="uk-button uk-button-danger" id="confirmDeleteMainCategory">刪除</button>
        </div>
    </div>
</div>

<script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}">

<script>
let currentDeleteCategoryId = null;

function confirmDeleteMainCategory(categoryId, categoryName) {
    currentDeleteCategoryId = categoryId;
    document.getElementById('deleteMainCategoryMessage').textContent = `您確定要刪除主分類「${categoryName}」嗎？`;
    $('#deleteMainCategoryModal').modal();
}

document.getElementById('confirmDeleteMainCategory').addEventListener('click', function() {
    if (currentDeleteCategoryId) {
        // 創建表單並提交
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('auctioneer/dashboard/main-categories') }}/${currentDeleteCategoryId}`;

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
