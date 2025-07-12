@extends('layouts.auctioneer')

@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">

            <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
                <ul id="validator-alert-ul"></ul>
            </div>
            <form class="uk-form-stacked" id="lot-form">
                <ul class="uk-child-width-expand" uk-tab>
                    <li><a href="#" class="uk-text-nowrap">物品資料</a></li>
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
                                    <input class="uk-input" id="lot-name" type="text" name="name" placeholder="請精簡介紹您的特色物品作為標題">
                                </div>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <div class="uk-card uk-card-default uk-card-body">
                                <h3 class="uk-card-title">分類</h3>
                                <label class="uk-form-label" for="sub-categories" style="margin-top: 1em;">為您的物品選擇子分類</label>
                                <div class="uk-form-controls">
                                    <select class="uk-select" id="main-category" name="mainCategoryId">
                                        <option selected disabled>選擇一個分類</option>
                                        @foreach ($mainCategories as $mainCategory)
                                            <option value="{{ $mainCategory->id }}">{{ $mainCategory->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="uk-form-label" for="sub-categories" style="margin-top: 1em;">為您的物品選擇子分類</label>
                                    <div class="uk-form-controls">
                                        <select class="uk-select" id="sub-categories" name="sub_category_id">
                                            <!-- sub categories options will be loaded here -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="uk-margin">
                            <div class="uk-card uk-card-default uk-card-body">
                                <h3 class="uk-card-title uk-form-label">規格</h3>
                                <div id="default-specifications"></div>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <div class="uk-card uk-card-default uk-card-body">
                                <h3 class="uk-card-title uk-form-label">描述您的物品</h3>
                                <textarea class="uk-textarea" rows="5" placeholder="描述您的物品" name="description"></textarea>
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
                                <ul id="sortable-list" class="uk-grid-small uk-child-width-1-4@m uk-child-width-1-2 uk-flex-center uk-flex-middle uk-grid" uk-grid uk-sortable="handle: .uk-card">
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
                                        <input class="uk-input" id="reserve-price" name="reserve_price" type="number">
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
                                        <input class="uk-input" id="inventory" name="inventory" type="number" value="1" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(Auth::user()->role === 2)
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <div class="uk-grid uk-grid-small" uk-grid>
                                        <div class="uk-width-expand">
                                            <h3 class="uk-card-title uk-form-label">是否寄給拍賣會委賣</h3>
                                        </div>
                                        <div class="uk-width-auto" style="padding-top: 5px">
                                            <label class="uk-switch" for="check-entrust">
                                                <input type="checkbox" id="check-entrust" name="entrust">
                                                <div class="uk-switch-slider"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </li>
                    <li>
                        <div class="uk-margin">
                            <div class="uk-card uk-card-default uk-card-body">
                                <div class="uk-grid uk-grid-small" uk-grid>
                                    <div class="uk-width-expand">
                                        <h3 class="uk-card-title uk-form-label">允許面交</h3>
                                    </div>
                                    <div class="uk-width-auto" style="padding-top: 5px">
                                        <label class="uk-switch" for="face-to-face">
                                            <input type="checkbox" id="face-to-face" name="faceToFace">
                                            <div class="uk-switch-slider"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <div class="uk-card uk-card-default uk-card-body">
                                <div class="uk-grid uk-grid-small" uk-grid>
                                    <div class="uk-width-expand">
                                        <h3 class="uk-card-title uk-form-label">允許宅配</h3>
                                    </div>
                                    <div class="uk-width-auto" style="padding-top: 5px">
                                        <label class="uk-switch" for="home-delivery">
                                            <input type="checkbox" id="home-delivery" name="homeDelivery">
                                            <div class="uk-switch-slider"></div>
                                        </label>
                                    </div>
                                </div>
                                <div class="uk-margin">
                                    <label class="uk-form-label uk-text-default">預估買家負擔宅配金額 (設置為0的話將由您負擔運費)</label>
                                    <div class="uk-inline" style="display: block;">
                                        <span class="uk-form-icon" uk-icon="icon: tag"></span>
                                        <input class="uk-input" type="number" id="home-delivery-cost" name="homeDeliveryCost"
                                               disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <div class="uk-card uk-card-default uk-card-body">
                                <div class="uk-grid uk-grid-small" uk-grid>
                                    <div class="uk-width-expand">
                                        <h3 class="uk-card-title uk-form-label">允許境外宅配</h3>
                                    </div>
                                    <div class="uk-width-auto" style="padding-top: 5px">
                                        <label class="uk-switch" for="cross-border-delivery">
                                            <input type="checkbox" id="cross-border-delivery" name="crossBorderDelivery">
                                            <div class="uk-switch-slider"></div>
                                        </label>
                                    </div>
                                </div>
                                <div class="uk-margin">
                                    <label class="uk-form-label uk-text-default">預估買家負擔宅配金額 (設置為0的話將由您負擔運費)</label>
                                    <div class="uk-inline" style="display: block;">
                                        <span class="uk-form-icon" uk-icon="icon: tag"></span>
                                        <input class="uk-input" type="number" id="cross-board-delivery-cost"
                                               name="crossBorderDeliveryCost" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="uk-margin uk-flex uk-flex-right">
                    <a href="#confirmCreate" rel="modal:open" class="uk-button custom-button-1">建立物品</a>
                    <div id="confirmCreate" class="modal">
                        <h2>確認建立物品嗎？</h2>
                        <p class="uk-text-right">
                            <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                            <a class="uk-button custom-button-1 uk-width-auto@s" id="submit-form">確認</a>
                        </p>
                    </div>
                </div>
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

        let addNewMainImage = function (input, placeToInsertImagePreview) {
            let reader = new FileReader();

            reader.onload = function (event) {
                img = new Image();
                img.src = event.target.result;
                $(placeToInsertImagePreview).append(img);
            }

            reader.readAsDataURL(input.files[0]);
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

            $('#submit-form').click(function (event) {
                Swal.showLoading()
                event.preventDefault(); // Prevent the form from submitting the traditional way

                let inputData = new FormData($('#lot-form')[0]);

                // Get the order of the list items
                $('#sortable-list li').each(function() {
                    let index = $(this).attr('data-index');
                    inputData.append('images[]', selectedFiles[index]); // Append files in the correct order
                });

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('auctioneer.products.store') }}',
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
