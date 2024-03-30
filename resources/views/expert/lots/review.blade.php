@extends('layouts.expert')

@section('content')
    <div class="uk-margin">
        <a href="{{ route('expert.lots.index', $mainCategory->id) }}" class="custom-link"> > 返回物品管理</a>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body">
            <h3 class="uk-card-title uk-form-label">賣家資料</h3>
            <div class="uk-form-controls">
                <ul class="uk-list">
                    <li>{{ $owner->name }}</li>
                    <li>{{ $owner->phone }}</li>
                </ul>
            </div>
        </div>
    </div>
    <hr>
    <ul class="uk-child-width-expand@s" uk-tab>
        <li class="uk-active"><a href="#">物品資料</a></li>
        <li><a href="#">圖片</a></li>
        <li><a href="#">拍賣設定</a></li>
        <li><a href="#">運送方式</a></li>
    </ul>
    <ul class="uk-switcher uk-margin">
        <li>
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title uk-form-label">商品名稱</h3>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="lot-name" type="text" value="{{ $lot->name }}" disabled>
                    </div>
                </div>
            </div>
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title uk-form-label">為您的物品選擇分類</h3>
                    <div class="uk-form-controls">
                        <select class="uk-select" id="main-category" name="mainCategoryId" disabled>
                            <option value="{{ $lot->categories->where('parent', null)->first()->id }}" selected>{{ $lot->categories->where('parent', null)->first()->name }}</option>
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
                                           value="{{ $specification->value }}" disabled>
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
                              disabled>{{  $lot->description  }}</textarea>
                </div>
            </div>
        </li>
        <li>
            <div class="uk-margin">
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title uk-form-label">上傳物品封面圖片</h3>
                        <div class="uk-margin">
                            <div class="uk-child-width-1-2"  id="main-image-preview">
                                <img src="{{ $lot->main_image->url }}">
                            </div>
                        </div>
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
                    </div>
                </div>
            </div>
        </li>
        <li>
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">底價</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="check-reserve-price">
                                <input type="checkbox" id="check-reserve-price" name="checkReversePrice"
                                       {{ ($lot->reserve_price === null ? '' : 'checked') }} disabled>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">設置底價（需高於NT$3000）</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" id="reserve-price" name="reserve_price" type="number"
                                value="{{ intval($lot->reserve_price) }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">是否寄給拍賣會委賣</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="check-entrust">
                                <input type="checkbox" id="check-entrust" {{ ($lot->entrust == 1 ? 'checked' : '') }} disabled>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
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
                                <input type="checkbox" id="face-to-face"
                                       {{ ($lot->deliveryMethods->pluck('code')->contains(0) == true ? 'checked' : '') }} disabled>
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
                                <input type="checkbox" id="home-delivery"
                                       {{ ($lot->deliveryMethods->pluck('code')->contains(1) == true ? 'checked' : '') }} disabled>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">預估買家負擔宅配金額 (設置為0的話將由委託者負擔運費)</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" type="number" id="home-delivery-fee" value="{{ intval(optional($lot->homeDelivery)->cost) }}" readonly>
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
                                <input type="checkbox" id="cross-border-delivery"
                                       {{ ($lot->deliveryMethods->pluck('code')->contains(2) == true ? 'checked' : '') }} disabled>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">預估買家負擔宅配金額 (設置為0的話將由委託者負擔運費)</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" type="number" id="cross-board-delivery-fee" value="{{ intval(optional($lot->crossBorderDelivery)->cost) }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
    <hr>
    <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
        <ul id="validator-alert-ul"></ul>
    </div>
    <input id="main-category-id" value="{{ $mainCategory->id }}" hidden>
    <input id="lot-id" value="{{ $lot->id }}" hidden>
    <form class="uk-form-stacked" method="POST"
          action="{{ route('expert.lots.handle', ['mainCategoryId'=>$mainCategory->id, 'lotId'=>$lot->id]) }}"
          enctype="multipart/form-data">
        @csrf
        <div class="uk-card uk-card-default uk-card-body custom-color-group-2">
            <h3 class="uk-card-title font-white">商品審核表</h3>
            <div class="uk-margin">
                <label class="uk-form-label font-white">設定商品子分類</label>
                <div class="uk-form-controls">
                    <select class="uk-select" id="sub-category-id" name="subCategoryId">
                        <option value="">選擇一個子分類</option>
                        @foreach($subCategories as $subCategory)
                            <option value="{{ $subCategory->id }}" {{ (in_array($subCategory->id, $lot->categories->pluck('id')->toArray()) == True ? 'selected' : '') }}>{{ $subCategory->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-form-label font-white">預估價格</label>
                <input class="uk-input" type="number" id="estimated-price" name="estimatedPrice" value="{{ intval($lot->estimated_price) ?? null }}">
            </div>
            <div class="uk-margin">
                <label class="uk-form-label font-white">起標價格</label>
                <input class="uk-input" type="number" id="starting-price" name="startingPrice" value="{{ intval($lot->starting_price) ?? null }}">
            </div>
            <div class="uk-margin">
                <label class="uk-form-label font-white">給予修改建議</label>
                <textarea class="uk-textarea" rows="5" placeholder="修改建議" id="suggestion" name="suggestion">{{ $lot->suggestion ?? null }}</textarea>
            </div>
        </div>

        <div id="request-revision" class="modal">
            <h2>確定要求修改嗎？</h2>
            <p class="uk-text-right">
                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                <button type="button" class="uk-button custom-button-2 handle" action="requestRevision" rel="modal:close">要求修改</button>
            </p>
        </div>

        <div id="accept-application" class="modal">
            <h2>確定審核通過嗎？</h2>
            <p class="uk-text-right">
                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                <button type="submit" class="uk-button custom-button-1 handle" action="acceptApplication">審核通過</button>
            </p>
        </div>

        <div id="update-bidding-info" class="modal">
            <h2>確定修改嗎？</h2>
            <p class="uk-text-right">
                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                <button type="submit" class="uk-button custom-button-1 handle" action="updateBiddingInfo">確定修改</button>
            </p>
        </div>

        @if($lot->status == 0)
            <div class="uk-margin uk-align-right">
                <a href="#request-revision" rel="modal:open" class="uk-button custom-button-2">要求修改</a>
                <a href="#accept-application" rel="modal:open" class="uk-button custom-button-1">審核通過</a>
            </div>
        @elseif($lot->status >= 10 && $lot->status <= 13)
            <div class="uk-margin uk-align-right">
                <a href="#update-bidding-info" rel="modal:open" class="uk-button custom-button-1">修改</a>
            </div>
        @endif

    </form>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
        $(function () {
            $('.handle').click(function () {
                let mainCategoryId = $('#main-category-id').val();
                let lotId = $('#lot-id').val();
                let url = '{{ route("expert.lots.handle", [":mainCategoryId", ":lotId"]) }}';
                url = url.replace(':mainCategoryId', mainCategoryId);
                url = url.replace(':lotId', lotId);

                let redirectUrl = '{{ route("expert.lots.review", [":mainCategoryId", ":lotId"]) }}';
                redirectUrl = redirectUrl.replace(':mainCategoryId', mainCategoryId);
                redirectUrl = redirectUrl.replace(':lotId', lotId);

                let action = $(this).attr('action');
                let startingPrice = $('#starting-price').val();
                let estimatedPrice = $('#estimated-price').val();
                let subCategoryId = $('#sub-category-id').val();
                let suggestion = $('#suggestion').val();


                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    data: { action:action, startingPrice:startingPrice, estimatedPrice:estimatedPrice, subCategoryId:subCategoryId, suggestion:suggestion, mainCategoryId:mainCategoryId },
                    url: url,
                    success: function (response) {
                        $.modal.close();

                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: '已送出',
                            showConfirmButton: false,
                        })

                        setTimeout(function() {
                            window.location.assign(redirectUrl);
                        }, 1000);
                    },
                    error: function (response) {
                        $.modal.close();
                        let errors = merge_errors(response)
                        let validatorAlert = $('#validator-alert');
                        validatorAlert.empty();
                        validatorAlert.prop('hidden', false);
                        validatorAlert.append(errors);
                    }
                });
            });
        });
    </script>
@endpush
