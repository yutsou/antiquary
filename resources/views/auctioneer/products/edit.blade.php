@extends('layouts.auctioneer')
@inject('lotEditPresenter', 'App\Presenters\LotEditPresenter')
@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-small">商品號碼: {{ $lot->id }}</h1>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">

            <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
                <ul id="validator-alert-ul"></ul>
            </div>
                <form class="uk-form-stacked" id="lot-form">
                    <ul class="uk-child-width-expand" uk-tab>
                        <li class="uk-active uk-text-nowrap"><a href="#">物品資料</a></li>
                        <li><a href="#" class="uk-text-nowrap">圖片</a></li>
                        <li><a href="#" class="uk-text-nowrap">商品設定</a></li>
                        <li><a href="#" class="uk-text-nowrap">運送方式</a></li>
                    </ul>
                    <ul class="uk-switcher uk-margin">
                        <li>
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title uk-form-label">商品名稱</h3>
                                    <div class="uk-form-controls">
                                        <input class="uk-input" id="lot-name" type="text" name="name" value="{{ $lot->name }}" placeholder="請精簡介紹您的特色物品作為標題">
                                    </div>
                                </div>
                            </div>
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title uk-form-label">自訂編號</h3>
                                    <div class="uk-form-controls">
                                        <input class="uk-input" id="custom-id" type="text" name="custom_id" value="{{ $lot->custom_id }}" placeholder="自訂商品編號 (可留空)">
                                    </div>
                                </div>
                            </div>
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title">分類</h3>
                                    <label class="uk-form-label" for="sub-categories" style="margin-top: 1em;">為您的物品選擇子分類</label>
                                    <div class="uk-form-controls">
                                        <select class="uk-select" id="main-category" name="mainCategoryId">
    <option disabled {{ empty($lot->main_category) ? 'selected' : '' }}>選擇一個分類</option>
    @foreach ($mainCategories as $mainCategory)
        <option value="{{ $mainCategory->id }}"
            {{ $lot->main_category->id == $mainCategory->id ? 'selected' : '' }}>
            {{ $mainCategory->name }}
        </option>
    @endforeach
</select>
                                        <label class="uk-form-label" for="sub-categories" style="margin-top: 1em;">為您的物品選擇子分類</label>
                                        <div class="uk-form-controls">
                                            <select class="uk-select" id="sub-categories" name="subCategoryId">
    @foreach ($subCategories as $subCategory)
        <option value="{{ $subCategory->id }}"
            {{ $lot->sub_category->id == $subCategory->id ? 'selected' : '' }}>
            {{ $subCategory->name }}
        </option>
    @endforeach
</select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title uk-form-label">規格</h3>
                                    <div id="default-specifications">
                                        @foreach ($lot->specifications as $specification)
                                            <div class="uk-margin uk-form-horizontal">
                                                <label class="uk-form-label">{{ $specification->title }}</label>
                                                <div class="uk-form-controls">
                                                    <input class="uk-input" type="text"
                                                           name="specificationValues[{{ $specification->id }}]"
                                                           value="{{ $specification->value }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title uk-form-label">描述您的物品</h3>
                                    <textarea class="uk-textarea" rows="5" placeholder="描述您的物品"
                                              name="description">{{  $lot->description  }}</textarea>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="uk-margin">

                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title uk-form-label">上傳物品圖片</h3>
                                    <p class="uk-text-meta">可經由拖曳改變圖片順序，第一張圖片為封面圖片。</p>
                                    <div class="uk-placeholder uk-text-center">
                                        <span uk-icon="icon: cloud-upload"></span>
                                        <span class="uk-text-middle">拖曳圖片到此處，或</span>
                                        <div uk-form-custom>
                                            <input type="file" multiple id="gallery" accept="image/jpeg, image/png">
                                            <span class="uk-link">選擇圖片</span>
                                        </div>
                                        <span class="uk-text-middle">(支援的圖片格式：jpg、png)</span>
                                    </div>
                                    <ul id="sortable-list" class="uk-grid-small uk-child-width-1-4 uk-flex-center uk-flex-middle uk-grid" uk-grid uk-sortable="handle: .uk-card">
                                    @foreach($lot->blImages as $image)
                                        <li id="{{ $image->id }}">
                                            <div class="uk-card uk-card-default uk-card-body uk-card-small uk-text-center">
                                                <img src="{{ $image->url }}" style="max-width: 100%">
                                            </div>
                                        </li>
                                    @endforeach
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <div class="uk-grid uk-grid-small" uk-grid>
                                        <div class="uk-width-expand">
                                            <h3 class="uk-card-title uk-form-label">價格</h3>
                                        </div>

                                    </div>
                                    <div class="uk-margin">
                                        <label class="uk-form-label uk-text-default">設置價格</label>
                                        <div class="uk-inline" style="display: block;">
                                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                                            <input class="uk-input" id="reserve-price" name="reserve_price" type="number" value="{{ intval($lot->reserve_price)}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <div class="uk-grid uk-grid-small" uk-grid>
                                        <div class="uk-width-expand">
                                            <h3 class="uk-card-title uk-form-label">庫存</h3>
                                        </div>

                                    </div>
                                    <div class="uk-margin">
                                        <label class="uk-form-label uk-text-default">設置庫存</label>
                                        <div class="uk-inline" style="display: block;">
                                            <input class="uk-input" id="inventory" name="inventory" type="number" value="{{ $lot->inventory }}" min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            {!! $lotEditPresenter->displayFaceToFace($lot) !!}
                            {!! $lotEditPresenter->displayHomeDelivery($lot) !!}
                            {!! $lotEditPresenter->displayCrossBorderDelivery($lot) !!}
                        </li>
                    </ul>
                    @if($lot->status == 60)
                        <div class="uk-margin uk-flex uk-flex-right">
                            <a href="#confirmEdit" rel="modal:open" class="uk-button custom-button-1">送出修改</a>
                            <div id="confirmEdit" class="modal">
                                <h2>確認送出修改嗎？</h2>
                                <p class="uk-text-right">
                                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                    <a class="uk-button custom-button-1 uk-width-auto@s" id="submit-edit">確認</a>
                                </p>
                            </div>
                        </div>
                    @endif
                </form>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
    <style>
        .uk-active > a {
            border-bottom: 2px solid #003a6c !important;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
        let getDefaultSpecification = function (mainCategoryId) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "get",
                url: '/auctioneer/ajax/main-categories/' + mainCategoryId + '/default-specification-titles',
                success: function (defaultSpecificationTitles) {
                    $('#default-specifications').empty();
                    for (let i = 0; i < defaultSpecificationTitles.length; i++) {
                        defaultSpecificationTitles[i];
                        $('#default-specifications').append(
                            $('<div>').addClass('uk-margin uk-form-horizontal').append(
                                $('<label>').addClass('uk-form-label').text(defaultSpecificationTitles[i]['title']).add(
                                    $('<input>').addClass('uk-input').attr('type', 'text').attr('name', 'specificationTitles[]').val(defaultSpecificationTitles[i]['title']).hide()
                                ).add(
                                    $('<div>').addClass('uk-form-controls').append(
                                        $('<input>').addClass('uk-input').attr('type', 'text').attr('name', 'specificationValues[]')
                                    )
                                )
                            )
                        )
                    }
                }
            });
        };

        let addNewShippingMethod = function () {
            $("#shipping-methods").append(
                $('<div>').addClass('uk-margin uk-form-horizontal').append(
                    $('<label>').addClass('uk-form-label').text(defaultSpecificationTitles[i]['title']).add(
                        $('<input>').addClass('uk-input').attr('type', 'text').attr('name', 'specificationTitles[]').val(defaultSpecificationTitles[i]['title']).hide()
                    ).add(
                        $('<div>').addClass('uk-form-controls').append(
                            $('<input>').addClass('uk-input').attr('type', 'text').attr('name', 'specificationValues[]')
                        )
                    )
                )
            )
        };

        let getSubCategories = function (mainCategoryId) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "get",
                url: '/auctioneer/ajax/main-categories/' + mainCategoryId + '/sub-categories',
                success: function (subCategories) {
                    $('#sub-categories').empty();
                    for (let i = 0; i < subCategories.length; i++) {
                        $('#sub-categories').append(
                            $('<option>').val(subCategories[i].id).text(subCategories[i].name)
                        );
                    }
                }
            });
        };
    </script>

    <script>
        $(function () {

            let selectedFiles = [];

            $("#main-category").change(function () {
                let mainCategoryId = $(this).val();
                getDefaultSpecification(mainCategoryId);
                getSubCategories(mainCategoryId);
            });

            $('#add-new-shipping-method').click(function () {
                addNewShippingMethod();
            });

            $('#main-image').on('change', function () {
                $('#main-image-preview').empty();
                addNewMainImage(this, '#main-image-preview');
            });

            $('#home-delivery').on('change', function () {
                if ($('#home-delivery-cost').is(':disabled') === true) {
                    $('#home-delivery-cost').prop("disabled", false);
                } else {
                    $('#home-delivery-cost').prop("disabled", true);
                }
            });

            $('#cross-border-delivery').on('change', function () {
                if ($('#cross-board-delivery-cost').is(':disabled') === true) {
                    $('#cross-board-delivery-cost').prop("disabled", false);
                } else {
                    $('#cross-board-delivery-cost').prop("disabled", true);
                }
            });

            $('#gallery').on('change', function(event) {
                $('#sortable-list').empty();

                selectedFiles = Array.from(event.target.files); // Convert FileList to Array

                $.each(selectedFiles, function(index, file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const li = $('<li>').append(
                            $('<div>').addClass('uk-card uk-card-default uk-card-body uk-card-small uk-text-center').append(
                                $('<img>').attr('src', e.target.result).css('max-width', '100%')
                            )
                        ).attr('data-index', index); // Store the file index in the list item
                        $('#sortable-list').append(li);
                    };

                    reader.readAsDataURL(file);
                });
            });

            let moved = 0;
            UIkit.util.on('#sortable-list', 'moved', function() {
                moved = 1;
            });

            $('#submit-edit').click(function (event) {
                Swal.showLoading()
                event.preventDefault(); // Prevent the form from submitting the traditional way

                let inputData = new FormData($('#lot-form')[0]);

                // Get the order of the list items

                if(selectedFiles.length !== 0) {
                    $('#sortable-list li').each(function() {
                        let index = $(this).attr('data-index');
                        inputData.append('images[]', selectedFiles[index]); // Append files in the correct order
                    });
                } else {
                    $('#sortable-list li').each(function() {
                        inputData.append('imageOrderArray[]', $(this).attr('id'));
                    });
                }

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('auctioneer.products.update', ['lotId'=>$lot->id]) }}',
                    data: inputData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: '已送出',
                            showConfirmButton: false,
                        })

                        function successAction (response) {
                            window.location.replace(response.success);
                        }

                        setTimeout(function (){
                            successAction(response)
                        }, 1500);
                    },
                    error: function (response) {
                        Swal.close()
                        let errors = merge_errors(response)
                        let validatorAlert = $('#validator-alert');
                        validatorAlert.empty();
                        validatorAlert.prop('hidden', false);
                        validatorAlert.append(errors);
                        $('html,body').animate({scrollTop: 0}, 500);
                    }
                });
                $.modal.close();
            });
        });
    </script>
@endpush
