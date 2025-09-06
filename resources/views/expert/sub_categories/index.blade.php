@extends('layouts.expert')

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
    <div class="uk-margin">
        <a href="{{ route('expert.sub_categories.create', $mainCategoryId) }}" class="uk-button custom-button-1">創建子分類</a>
    </div>
    <div class="uk-margin-medium">
        <div class="uk-grid-column-small uk-grid-row-large uk-child-width-1-5@s uk-text-center" uk-grid>
            @foreach ($subCategories as $subCategory)
                <div>
                    <div class="uk-card uk-card-default uk-card-body uk-card-small">
                        <div class="uk-margin-small-bottom">
                            <strong>{{ $subCategory->name }}</strong>
                        </div>
                        <div>
                            <a href="{{ route('expert.sub_categories.edit', [$mainCategoryId, $subCategory->id]) }}"
                               class="uk-button uk-button-small uk-button-primary" style="margin-right: 5px;">編輯</a>
                            <button class="uk-button uk-button-small uk-button-danger"
                                    onclick="confirmDeleteSubCategory({{ $subCategory->id }}, '{{ $subCategory->name }}')">刪除</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

<!-- Delete Confirmation Modal -->
<div id="deleteSubCategoryModal" class="modal">
    <div class="modal-content">
        <h3>確認刪除</h3>
        <p id="deleteSubCategoryMessage"></p>
        <div class="modal-buttons">
            <button class="uk-button uk-button-default" onclick="$.modal.close()">取消</button>
            <button class="uk-button uk-button-danger" id="confirmDeleteSubCategory">刪除</button>
        </div>
    </div>
</div>

<script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}">

<script>
let currentDeleteSubCategoryId = null;

function confirmDeleteSubCategory(categoryId, categoryName) {
    currentDeleteSubCategoryId = categoryId;
    document.getElementById('deleteSubCategoryMessage').textContent = `您確定要刪除子分類「${categoryName}」嗎？`;
    $('#deleteSubCategoryModal').modal();
}

document.getElementById('confirmDeleteSubCategory').addEventListener('click', function() {
    if (currentDeleteSubCategoryId) {
        // 創建表單並提交
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('expert/dashboard/main-categories') }}/{{ $mainCategoryId }}/sub-categories/${currentDeleteSubCategoryId}`;

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
