@extends('layouts.member')
@inject('carbonPresenter', 'App\Presenters\CarbonPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>

    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>

            <!-- 錯誤訊息顯示區塊 -->
            @if(session('error'))
                <div class="uk-alert uk-alert-danger" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- 成功訊息顯示區塊 -->
            @if(session('success'))
                <div class="uk-alert uk-alert-success" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- 合併運費請求區塊 -->
            @if($mergeShippingRequests && $mergeShippingRequests->count() > 0)
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">合併運費請求</h3>
                        @foreach($mergeShippingRequests as $request)
                            <div class="uk-card uk-card-default uk-margin">
                                <div class="uk-card-body">
                                    <div class="uk-grid-small" uk-grid>
                                        <div class="uk-width-expand">
                                            <h4 class="uk-card-title">{{ $request->delivery_method_text }} - {{ $request->status_text }}</h4>
                                            <p>原本運費: NT${{ number_format($request->original_shipping_fee) }}</p>
                                            @if($request->new_shipping_fee)
                                                <p>新運費: NT${{ number_format($request->new_shipping_fee) }}</p>
                                            @endif
                                            @if($request->remark)
                                                <p>備註: {{ $request->remark }}</p>
                                            @endif
                                            <p class="uk-text-small">申請時間: {{ $request->created_at->format('Y-m-d H:i') }}</p>
                                        </div>
                                        <div class="uk-width-auto">
                                            @if($request->status == 0)
                                                <span class="uk-label uk-label-warning">待處理</span>
                                            @elseif($request->status == 1)
                                                <span class="uk-label uk-label-success">已處理</span>
                                            @elseif($request->status == 2)
                                                <span class="uk-label uk-label-danger">物品不支援運費合併</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="uk-margin">
                                        <h5>包含商品:</h5>
                                        <div class="uk-grid-small uk-child-width-1-3" uk-grid>
                                            @foreach($request->items as $item)
                                                <div>
                                                    <div class="uk-card uk-card-default uk-card-small">
                                                        <div class="uk-card-media-top">
                                                            <img src="{{ $item->lot->blImages->first()->url }}" alt="" style="height: 100px; object-fit: cover;">
                                                        </div>
                                                        <div class="uk-card-body">
                                                            <h6 class="uk-card-title">{{ $item->lot->name }}</h6>
                                                            <p class="uk-text-small">數量: {{ $item->quantity }}</p>
                                                            @if($request->status == 0)
                                                            <p class="uk-text-small">運費: NT${{ number_format($item->original_shipping_fee) }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if($request->status == 1)
                                        <div class="uk-margin uk-text-right">
                                            <a href="{{ route('account.cart.merge_shipping.delivery_method.edit', $request->id) }}" class="uk-button custom-button-1">
                                                進行結帳
                                            </a>
                                        </div>
                                    @elseif($request->status == 2)
                                        <div class="uk-margin uk-text-right">
                                            <button
                                                type="button"
                                                class="uk-button custom-button-2 uk-button-small remove-merge-request"
                                                data-request-id="{{ $request->id }}"
                                            >
                                                移除
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <form id="cart-form" method="POST" action="{{ route('account.cart.delivery_method_choice') }}">
                    @csrf
                    @foreach($cartItems as $lot)
                        @if($lot->type === 1)
                            <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                                <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                                    <img src="{{ $lot->blImages->first()->url }}" alt="" uk-cover>
                                </div>
                                <div class="uk-width-expand">
                                    <div class="uk-card-body" style="padding: 20px 20px">
                                        <div class="uk-margin uk-text-right">
                                            <button
                                                type="button"
                                                class="uk-button uk-button-small custom-button-2 remove-cart-item"
                                                data-lot-id="{{ $lot->id }}"
                                            >
                                                移除
                                            </button>
                                        </div>
                                        <hr>
                                        <h3 class="uk-card-title" style="margin: 0 0 0 0">{{ $lot->name }}</h3>
                                        <hr>
                                        <div class="uk-margin uk-text-right">
                                            <label for="cart-quantity-{{ $lot->id }}" class="uk-margin-small-right">數量:</label>
                                            <input
                                                type="number"
                                                id="cart-quantity-{{ $lot->id }}"
                                                name="cart_quantities[{{ $lot->id }}]"
                                                class="uk-input cart-quantity-input"
                                                style="width: 80px; display: inline-block;"
                                                min="1"
                                                max="{{ $lot->inventory ?? 99 }}"
                                                value="{{ $lot->cart_quantity }}"
                                                data-lot-id="{{ $lot->id }}"
                                            />
                                            <span class="uk-margin-small-left">| 小計: NT${{ number_format($lot->subtotal) }}</span>
                                            <label class="uk-margin-small-left">
                                                <input
                                                    type="checkbox"
                                                    class="uk-checkbox cart-item-checkbox"
                                                    name="selected_lots[]"
                                                    value="{{ $lot->id }}"
                                                > 選擇本商品
                                            </label>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($lot->type === 0)
                            <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                                <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                                    <img src="{{ $lot->blImages->first()->url }}" alt="" uk-cover>
                                </div>
                                <div class="uk-width-expand">
                                    <div class="uk-card-body" style="padding: 20px 20px">
                                        <div class="uk-margin uk-text-right">
                                            <span class="uk-label uk-label-warning">競標商品</span>
                                        </div>
                                        <hr>
                                        <h3 class="uk-card-title" style="margin: 0 0 0 0">{{ $lot->name }}</h3>
                                        <hr>
                                        <div class="uk-margin uk-text-right">
                                            <span class="uk-margin-small-right">數量: {{ $lot->cart_quantity }}</span>
                                            <span class="uk-margin-small-left">| 單價: NT${{ number_format($lot->current_bid) }}</span>
                                            <span class="uk-margin-small-left">| 小計: NT${{ number_format($lot->subtotal) }}</span>
                                            <label class="uk-margin-small-left">
                                                <input
                                                    type="checkbox"
                                                    class="uk-checkbox cart-item-checkbox"
                                                    name="selected_lots[]"
                                                    value="{{ $lot->id }}"
                                                > 選擇本商品
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                                <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                                    <img src="{{ $lot->blImages->first()->url }}" alt="" uk-cover>
                                </div>
                                <div class="uk-width-expand">
                                    <div class="uk-card-body" style="padding: 20px 20px">
                                        <div class="uk-margin uk-text-right">
                                            <p>{!! $carbonPresenter->lotPresent($lot->id, $lot->auction_end_at) !!}</p>
                                        </div>
                                        <hr>
                                        <h3 class="uk-card-title" style="margin: 0 0 0 0">{{ $lot->name }}</h3>
                                        <hr>
                                        <div class="uk-margin uk-text-right">
                                            <a href="{{ route('mart.lots.show', ['lotId'=>$lot->id]) }}"
                                            class="uk-button custom-button-1">前往競標</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if($cartItems->whereIn('type', [0, 1])->count() > 0)
                        <div class="uk-margin uk-text-right">
                            <button type="submit" class="uk-button custom-button-1" id="checkout-btn" disabled>
                                結帳 (<span id="selected-count">0</span> 件商品)
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        class UnitDate {
            constructor (date) {
                let { userAgent } = window.navigator;
                if (userAgent.includes('Safari')) {
                    if (typeof date === 'string') {
                        date = date.replace(/-/g, '/');
                        return new Date(date);
                    }
                    return new Date(date);
                }
                return new Date(date);
            }
        }
    </script>
    <script>
        $(function () {
            $('.cart-quantity-input').on('change', function() {
            var lotId = $(this).data('lot-id');
            var quantity = $(this).val();

            // 發送 AJAX 到後端
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: '{{ route('account.cart.update') }}', // 請替換成你的更新購物車 API 路徑
                data: {
                    lot_id: lotId,
                    quantity: quantity
                },
                success: function(response) {
                    // 更新成功，重新整理頁面
                    location.reload();
                },
                error: function(xhr) {
                    // 顯示錯誤訊息
                    let errorMsg = '更新數量失敗！';
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).map(function(arr){
                            return arr.join('<br>');
                        }).join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        html: errorMsg,
                        showConfirmButton: false,
                        timer: 2000,
                    });
                }
            });
        });

            // 處理商品選擇
            $('.cart-item-checkbox').on('change', function() {
                updateCheckoutButton();
            });

            function updateCheckoutButton() {
                var selectedCount = $('.cart-item-checkbox:checked').length;
                $('#selected-count').text(selectedCount);

                if (selectedCount > 0) {
                    $('#checkout-btn').prop('disabled', false);
                } else {
                    $('#checkout-btn').prop('disabled', true);
                }
            }

            // 初始化結帳按鈕狀態
            updateCheckoutButton();

            // 處理移除商品
            $('.remove-cart-item').on('click', function() {
                var lotId = $(this).data('lot-id');
                var $cartItem = $(this).closest('.uk-card');

                // 直接發送 AJAX 到後端移除商品
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: '{{ route('account.cart.remove') }}',
                    data: {
                        lot_id: lotId
                    },
                    success: function(response) {
                        // 移除成功，隱藏該商品卡片
                        $cartItem.fadeOut(300, function() {
                            $(this).remove();
                            // 檢查是否還有商品
                            if ($('.cart-item-checkbox').length === 0) {
                                location.reload(); // 如果沒有商品了，重新整理頁面
                            }
                            // 更新結帳按鈕狀態
                            updateCheckoutButton();
                        });

                        // 更新購物車數量
                        if(response.cart_count !== undefined){
                            $('#cart-count').text(response.cart_count);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: '商品已移除！',
                            showConfirmButton: false,
                            timer: 1500,
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = '移除商品失敗！';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).map(function(arr){
                                return arr.join('<br>');
                            }).join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            html: errorMsg,
                            showConfirmButton: false,
                            timer: 2000,
                        });
                    }
                });
            });

            // 處理移除合併運費請求
            $('.remove-merge-request').on('click', function() {
                var requestId = $(this).data('request-id');
                var $requestCard = $(this).closest('.uk-card');

                // 直接發送 AJAX 到後端移除合併運費請求
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: '{{ route('account.cart.merge_shipping_request.remove', ':requestId') }}'.replace(':requestId', requestId),
                    success: function(response) {
                        // 移除成功，隱藏該請求卡片
                        $requestCard.fadeOut(300, function() {
                            $(this).remove();
                            // 檢查是否還有合併運費請求
                            if ($('.uk-card').length === 0) {
                                location.reload(); // 如果沒有請求了，重新整理頁面
                            }
                        });

                        Swal.fire({
                            icon: 'success',
                            title: '合併運費請求已移除！',
                            showConfirmButton: false,
                            timer: 1500,
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = '移除合併運費請求失敗！';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).map(function(arr){
                                return arr.join('<br>');
                            }).join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            html: errorMsg,
                            showConfirmButton: false,
                            timer: 2000,
                        });
                    }
                });
            });
        });
    </script>
@endpush
