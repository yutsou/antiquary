@extends('layouts.member')
@inject('lotEditPresenter', 'App\Presenters\LotEditPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.applications.index') }}" class="custom-color-1 custom-link-mute">審核中的申請</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            @if(isset($lot->suggestion))
                <div class="uk-margin">
                    <div uk-alert class="uk-alert-primary">
                        <a class="uk-alert-close" uk-close></a>
                        <h3>專家建議</h3>
                        <p>{!! nl2br($lot->suggestion) !!}</p>
                    </div>
                </div>
            @endif
            <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
                <ul id="validator-alert-ul"></ul>
            </div>
                <form class="uk-form-stacked" method="POST" id="lot-form"  enctype="multipart/form-data">
                    @csrf
                    <ul class="uk-child-width-expand" uk-tab>
                        <li class="uk-active uk-text-nowrap"><a href="#">物品資料</a></li>
                        <li><a href="#" class="uk-text-nowrap">圖片</a></li>
                        <li><a href="#" class="uk-text-nowrap">拍賣設定</a></li>
                        <li><a href="#" class="uk-text-nowrap">運送方式</a></li>
                    </ul>
                    <ul class="uk-switcher uk-margin">
                        <li>
                            <div class="uk-margin">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title uk-form-label">為您的物品選擇分類</h3>
                                    <div class="uk-form-controls">
                                        <select class="uk-select" id="main-category" name="mainCategoryId">
                                            @foreach ($mainCategories as $mainCategory)
                                                <option value="{{ $mainCategory->id }}"
                                                    {{ ($lot->main_category->id === $mainCategory->id ? 'selected' : '') }}
                                                >{{ $mainCategory->name }}</option>
                                            @endforeach
                                        </select>
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
                                <div class="uk-margin">
                                    <div class="uk-card uk-card-default uk-card-body">
                                        <h3 class="uk-card-title uk-form-label">上傳物品封面圖片</h3>
                                        <div class="uk-margin">
                                            <div class="uk-child-width-1-2" id="main-image-preview">
                                                <img src="{{ $lot->main_image->url }}">
                                            </div>
                                        </div>
                                        @if($lot->status == 1)
                                            <div class="js-upload" uk-form-custom>
                                                <input type="file" id="main-image" name="mainImage">
                                                <button class="uk-button uk-button-default custom-color-group-1" type="button"
                                                        tabindex="-1">選擇圖片
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="uk-margin">
                                    <div class="uk-card uk-card-default uk-card-body">
                                        <h3 class="uk-card-title uk-form-label">上傳物品其他圖片</h3>
                                        <div class="uk-margin">
                                            <div class="uk-grid-small uk-child-width-1-2 uk-child-width-1-4@s gallery" uk-grid>
                                                @foreach ($lot->other_images as $image)
                                                    <div>
                                                        <img src="{{ $image->url }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @if($lot->process == 0 && $lot->status == 0)
                                            <div class="js-upload" uk-form-custom>
                                                <input type="file" multiple id="gallery-photo-add" name="images[]">
                                                <button class="uk-button uk-button-default custom-color-group-1" type="button"
                                                        tabindex="-1">選擇圖片
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            {!! $lotEditPresenter->displayReservePrice($lot) !!}
                            {!! $lotEditPresenter->displayEntrust($lot) !!}
                        </li>
                        <li>
                            {!! $lotEditPresenter->displayFaceToFace($lot) !!}
                            {!! $lotEditPresenter->displayHomeDelivery($lot) !!}
                            {!! $lotEditPresenter->displayCrossBorderDelivery($lot) !!}
                        </li>
                    </ul>
                    @if($lot->status == 1)
                        <div class="uk-margin uk-flex uk-flex-right">
                            <button type="submit" class="uk-button custom-button-1 uk-width-auto@s">修改</button>
                        </div>
                    @endif
                </form>
        </div>
    </div>
@endsection
@push('style')
    <style>
        .uk-active > a {
            border-bottom: 2px solid #003a6c !important;
        }
    </style>
@endpush
@push('scripts')
    <script>
        let getDefaultSpecification = function (mainCategoryId) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "get",
                url: '/account/ajax/main-categories/' + mainCategoryId + '/sub-categories',
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

        // Multiple images preview in browser
        let imagesPreview = function (input, placeToInsertImagePreview) {
            if (input.files) {
                let filesAmount = input.files.length;
                for (i = 0; i < filesAmount; i++) {
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        img = new Image();
                        img.src = event.target.result;
                        $(placeToInsertImagePreview).append($('<div>').append(img));
                        //$('<li>').append(img).appendTo(placeToInsertImagePreview);
                        //$('<img>').attr('src', event.target.result).appendTo(placeToInsertImagePreview);
                    }
                    reader.readAsDataURL(input.files[i]);
                }
            }
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
    </script>

    <script>
        $(function () {

            $("#main-category").change(function () {
                let mainCategoryId = $(this).val();
                getDefaultSpecification(mainCategoryId);
            });

            $('#gallery-photo-add').on('change', function () {
                $('.gallery').empty();
                imagesPreview(this, '.gallery');
            });

            $('#add-new-shipping-method').click(function () {
                addNewShippingMethod();
            });

            $('#main-image').on('change', function () {
                $('#main-image-preview').empty();
                addNewMainImage(this, '#main-image-preview');
            });

            $('#check-reserve-price').on('change', function () {
                if ($('#reserve-price').is(':disabled') === true) {
                    $('#reserve-price').prop("disabled", false);
                } else {
                    $('#reserve-price').prop("disabled", true);
                }
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

            $('#lot-form').submit(function (e) {
                e.preventDefault();
                let inputData = new FormData(this);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('account.applications.update', ['lotId'=>$lot->id]) }}',
                    data: inputData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        if (typeof (data.error) !== 'undefined') {
                            let errors = Object.values(data.error)
                            $('#validator-alert').prop('hidden', false);
                            let validatorAlertUl = $('#validator-alert-ul');
                            validatorAlertUl.empty();
                            errors.forEach(i => validatorAlertUl.append($("<li></li>").text(i)));
                            $('html,body').animate({scrollTop: 0}, 500);
                        } else {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: '已送出',
                                showConfirmButton: false,
                            })

                            setTimeout(function() {
                                window.location.assign('{{ route('account.applications.index') }}');
                            }, 1000);
                        }
                    }
                });
            });

        });
    </script>
@endpush
