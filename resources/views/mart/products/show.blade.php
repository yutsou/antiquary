@extends('layouts.member')

@section('content')
    @if ($errors->any())
        <script>
            $(function () {
                UIkit.notification({
                    message: '<div class="uk-text-center"><span uk-icon=\'icon:  warning\'></span> {{ $errors->first() }}</div>',
                    status: 'warning',
                    pos: 'top-center',
                    timeout: 2000
                });
            });
        </script>
    @endif
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a
            href="{{ route('mart.m_categories.show', $mCategory->id) }}"
            class="custom-color-1 custom-link-mute">{{ $mCategory->name }}</a> > <a
            href="{{ route('mart.s_categories.show', [$mCategory->id, $sCategory->id]) }}"
            class="custom-color-1 custom-link-mute">{{ $sCategory->name }}</a> > <a href="{{ URL::current() }}"
                                                                                    class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin">
        <div>
            <div style="font-size: 14px">商品號碼: {{ $lot->id }}</div>
        </div>
        <div class="uk-grid-medium" uk-grid>
            <div class="uk-width-2-3@m">
                <div class="uk-text-center"><h1 class="lot-head" style="color: #333;">{{ $head }}</h1></div>
                <div class="uk-position-relative" uk-slideshow="">
                    <ul class="uk-slideshow-items">
                        @foreach($lot->blImages as $index=>$image)
                            <li>
                                <div id="ex{{$index}}" class="modal lot-modal">
                                    <div class="uk-flex uk-flex-center">
                                        <img src="{{ $image->url }}" alt="" class="lot-modal-img">
                                    </div>
                                </div>
                                <a href="#ex{{$index}}" rel="modal:open">
                                    <img src="{{ $image->url }}" alt="" style="height: 100%;" class="uk-align-center">
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="uk-margin">
                        <div class="uk-flex uk-flex-center">
                            <div style="overflow-x: scroll; height: 120px;">
                                <ul class="uk-thumbnav uk-slider-items uk-grid-small" uk-grid
                                    style="touch-action: auto !important;">
                                    @foreach($lot->blImages as $key=>$image)
                                        <li uk-slideshow-item="{{ $key }}">
                                            <a href="#">
                                                <img src="{{ $image->url }}" alt=""
                                                     style="width: auto; height: 100px; object-fit: cover;">
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-margin-medium">
                    <div class="uk-text-right">
                        @if(Auth::check())
                            <a class="custom-link" id="favorite">
                                @if(Auth::user()->getFavoriteAttribute($lot->id) == false)
                                    <span id="favoriteStatus" class="google-icon">
                                        <span class="material-symbols-outlined uk-text-middle">favorite</span>
                                        <span id="favoriteStatusText" class="uk-text-middle">加到追蹤清單</span>
                                    </span>
                                @else
                                    <span id="favoriteStatus" class="google-icon-fill">
                                        <span class="material-symbols-outlined uk-text-middle">favorite</span>
                                        <span id="favoriteStatusText" class="uk-text-middle">已加到追蹤清單</span>
                                    </span>
                                @endif
                            </a>
                        @else
                            <!-- Modal HTML embedded directly into document -->
                            <div id="favorite-login-notice" class="modal">
                                <p class="uk-text-left uk-text-large">物品加入追蹤清單前需要先登入</p>
                                <p class="uk-text-right">
                                    <a class="uk-button custom-button-1"
                                    href="{{ route('login.show', ['redirectUrl'=> route('mart.products.show', $lot->id)]) }}">登入</a>                                </p>
                            </div>

                            <!-- Link to open the modal -->
                            <a href="#favorite-login-notice" class="custom-link" rel="modal:open">
                                <span id="favoriteStatus" class="google-icon">
                                    <span class="material-symbols-outlined uk-text-middle">favorite</span>
                                    <span id="favoriteStatusText" class="uk-text-middle">加到追蹤清單</span>
                                </span>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="uk-visible@m">
                    <div class="uk-margin">
                        <div class="custom-color-group-1" style="border-radius: 20px;">
                            <h3 style="color: #fff; padding: 0.5em 0 0.5em 1em;">商品規格</h3>
                        </div>
                        <table class="uk-table">
                            <tbody>
                            <tr>
                                <td class="uk-width-1-5 uk-text-nowrap">分類</td>
                                <td>{{ $lot->main_category->name }}</td>
                            </tr>
                            @foreach($lot->specifications as $specification)
                                <tr>
                                    <td class="uk-text-nowrap">{{ $specification->title }}</td>
                                    <td>{{ $specification->value }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="uk-margin">
                        <div class="custom-color-group-1" style="border-radius: 20px;">
                            <h3 style="color: #fff; padding: 0.5em 0 0.5em 1em;">
                                商品詳情</h3>
                        </div>
                        <div style="box-sizing: border-box; border-right: 1em #fff solid; border-left: 1em #fff solid">
                            <p>
                                {!! nl2br($lot->description) !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="uk-width-expand@m">
                <div class="uk-text-center custom-color-group-1" style="border-radius: 20px;">
                    <h3 style="color: #fff; padding: 0.5em 0 0.5em 0;">商品資訊</h3>
                </div>

                <div class="uk-margin">
                    <hr>

                    <!-- 配送方式顯示 -->
                    <div class="uk-margin">
                        <ul>
                            @if($lot->deliveryMethods->pluck('code')->contains(0))
                                <li>提供面交</li>
                            @else
                                <li><del>提供面交</del></li>
                            @endif

                            @if($lot->deliveryMethods->pluck('code')->contains(1))
                                <li>提供宅配 -
                                    得標者需支付運費NT${{ number_format($lot->getHomeDeliveryAttribute()->cost) }}</li>
                            @else
                                <li><del>提供宅配</del></li>
                            @endif

                            @if($lot->deliveryMethods->pluck('code')->contains(2))
                                <li>提供境外宅配 -
                                    得標者需支付運費NT${{ number_format($lot->getCrossBorderDeliveryAttribute()->cost) }}</li>
                            @else
                                <li><del>提供境外宅配</del></li>
                            @endif
                        </ul>
                    </div>

                    <hr>

                    <div class="uk-margin">
                        <div>
                            商品售價: NT$<span id="currentBid">{{ number_format($lot->reserve_price) }}</span>
                        </div>
                    </div>

                    <hr>

                    <div class="uk-margin">
                        <div class="uk-text-meta uk-margin-small-bottom">
                            庫存數量：{{ $lot->inventory ?? 0 }}
                        </div>
                        <label for="buy-quantity" class="uk-form-label">購買數量</label>
                        <input
                            class="uk-input"
                            id="buy-quantity"
                            name="quantity"
                            type="number"
                            min="1"
                            max="{{ $lot->inventory ?? 99 }}"
                            value="1"
                        >
                    </div>

                    <div class="uk-margin">
                        @if(Auth::check())
                            <a
                                id="add-to-cart"
                                class="uk-button custom-button-1 uk-width-expand"
                                data-lot-id="{{ $lot->id }}"
                            >加入購物車</a>
                        @else
                            <!-- Modal HTML embedded directly into document -->
                            <div id="cart-login-notice" class="modal">
                                <p class="uk-text-left uk-text-large">物品加入購物車前需要先登入</p>
                                <p class="uk-text-right">
                                    <a class="uk-button custom-button-1"
                                       href="{{ route('login.show', ['redirectUrl'=> request()->url()]) }}">登入</a>
                                </p>
                            </div>

                            <!-- Link to open the modal -->
                            <a href="#cart-login-notice" class="uk-button custom-button-1 uk-width-expand" rel="modal:open">
                                加入購物車
                            </a>
                        @endif
                    </div>
                </div>

            </div>
            <div class="uk-hidden@m">
                <div>
                    <ul class="uk-child-width-expand" uk-tab>
                        <li class="uk-active"><a href="#">商品規格</a></li>
                        <li><a href="#">商品詳情</a></li>
                    </ul>
                    <ul class="uk-switcher uk-margin">
                        <li>
                            <table class="uk-table">
                                <tbody>
                                <tr>
                                    <td class="uk-width-1-5 uk-text-nowrap">分類</td>
                                    <td>{{ $lot->main_category->name }}</td>
                                </tr>
                                @foreach($lot->specifications as $specification)
                                    <tr>
                                        <td class="uk-text-nowrap">{{ $specification->title }}</td>
                                        <td>{{ $specification->value }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </li>
                        <li>
                            <div
                                style="box-sizing: border-box; border-right: 1em #fff solid; border-left: 1em #fff solid">
                                <p>
                                    {!! nl2br($lot->description) !!}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="uk-margin-medium">
            @if($productsByCategory && count($productsByCategory) > 0)
        @foreach($productsByCategory as $categoryData)
                <section class="category-section">
                    <h3 style="text-size: 1.2em; font-weight: 500;" class="custom-color-1">{{ $categoryData['category']->name }}的其他商品</h3>

                    <!-- Desktop Category Slider -->
            <div class="uk-visible@m">
                        <div class="uk-slider-container-offset modern-slider" uk-slider="finite: true; sets: true;">
                            <div class="uk-position-relative uk-visible-toggle" tabindex="-1">
                        <ul class="uk-slider-items uk-child-width-1-4@s uk-grid-match" uk-grid>
                            @foreach($categoryData['lots'] as $product)
                            @if($product->id != $lot->id)
                                <li>
                                            <div class="modern-card product-card-click" productId="{{ $product->id }}">
                                                <div class="card-image-container">
                                                    <img src="{{ $product->blImages->first()->url }}" alt="{{ $product->name }}" class="card-image">
                                                    <div class="card-overlay">
                                                        <div class="overlay-content">
                                                            <span class="material-symbols-outlined overlay-icon">visibility</span>
                                                            <span class="overlay-text">查看詳情</span>
                                                        </div>
                                                    </div>
                                        </div>
                                                <div class="card-content">
                                                    <h3 class="card-title">{{ $product->name ?? '無名稱' }}</h3>
                                                    <div class="card-price">
                                                        <span class="price-value">NT${{ number_format($product->reserve_price) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                                <a class="uk-position-center-left uk-position-small uk-hidden-hover slider-nav-btn" href="#" uk-slidenav-previous
                            uk-slider-item="previous"></a>
                                <a class="uk-position-center-right uk-position-small uk-hidden-hover slider-nav-btn" href="#" uk-slidenav-next
                            uk-slider-item="next"></a>
                    </div>
                            <ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin slider-dots"></ul>
                </div>
            </div>

                    <!-- Mobile Category Slider -->
            <div class="uk-hidden@m">
                        <div class="uk-position-relative uk-visible-toggle" tabindex="-1" uk-slider="center: true">
                    <ul class="uk-slider-items uk-grid uk-grid-small uk-grid-match" uk-height-viewport="offset-top: true; offset-bottom: 75" uk-grid>
                        @foreach($categoryData['lots'] as $product)
                            @if($product->id != $lot->id)
                            <li class="uk-width-5-6">
                                        <div class="mobile-card product-card-click" productId="{{ $product->id }}">
                                            <div class="mobile-card-image-container">
                                                <img src="{{ $product->blImages->first()->url }}" alt="{{ $product->name }}" class="mobile-card-image">
                                                <div class="mobile-card-overlay">
                                                    <div class="mobile-overlay-content">
                                                        <span class="material-symbols-outlined mobile-overlay-icon">visibility</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mobile-card-content">
                                                <h3 class="mobile-card-title">{{ $product->name ?? '無名稱' }}</h3>
                                        </div>
                                            <div class="mobile-card-price-container">
                                                <div class="mobile-card-price">
                                                    <span class="mobile-price-value">NT${{ number_format($product->reserve_price) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
                </section>
        @endforeach
    @endif
        </div>


    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}"
          crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/hero.css') }}">
    <style>
        .uk-active > a {
            border-bottom: 2px solid #003a6c !important;
        }
    </style>
    <style>
        .lot-head {
            font-size: 2.0em;
        }

        @media (min-width: 1200px) {

            .lot-head {
                font-size: 2.5em;
            }

        }
    </style>
    <style>
        .lot-modal-img {
            max-width: 100vw;
            width: 75vw;
            max-height: 100vh;
            height: auto;
        }

        .lot-modal {
            max-width: 100vw;
            max-height: 100vh;
        }

        @media (min-width: 1200px) {
            .lot-modal-img {
                max-width: 75vw;
                width: auto;
                max-height: 75vh;
                height: 75vh;
            }
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
        class UnitDate {
            constructor(date) {
                let {userAgent} = window.navigator;
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
        let addToCart = function(lotId, quantity = 1) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "post",
                url: '{{ route('account.cart.store') }}',
                data: { lot_id: lotId, quantity: quantity },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '已加入購物車！',
                        showConfirmButton: false,
                        timer: 1000,
                    });
                    // 刷新購物車數量
                    if(response.cart_count !== undefined){
                        $('#cart-count').text(response.cart_count);
                    }

                    function successAction (response) {
                        window.location.replace(response.success);
                    }

                    setTimeout(function (){
                        successAction(response)
                    }, 1100);
                },
                error: function(xhr) {
                    let errorMsg = '';
                    switch(xhr.status) {
                        case 422:
                            errorMsg = '加入購物車失敗！';
                            break;
                        case 403:
                            errorMsg = '需先進行信箱驗證！';
                            break;
                        default:
                            errorMsg = '加入購物車失敗！';
                    }

                    let errors = xhr.responseJSON.errors;
                    if (errors) {
                        errorMsg = Object.values(errors).map(function(arr){
                            return arr.join('<br>');
                        }).join('<br>');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: errorMsg,
                        html: '', // 如果需要支援多行訊息可寫在 html
                        showConfirmButton: false,
                        timer: 2000,
                    });

                    if (xhr.status === 403) {
                        // 如果是403錯誤，跳轉到個人資料編輯頁面

                        setTimeout(function (){
                           window.location.replace("{{ route('account.profile.edit')  }}");
                        }, 2100);
                    }
                    r


                }
            });
        }
        $(function () {
            $('#add-to-cart').click(function(){
                let lotId = $(this).data('lot-id');
                let quantity = parseInt($('#buy-quantity').val()) || 1;
                addToCart(lotId, quantity);
            });
        });
    </script>
    <script>
        let addFavorite = function (lotId) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "post",
                url: '/account/ajax/lots/' + lotId + '/favorite',
                data: {lotId: lotId},
                success: function (status) {
                    if (status === 'added') {
                        $('#favoriteStatusText').text('已加到追蹤清單');
                        $('#favoriteStatus').removeClass('google-icon').addClass('google-icon-fill');
                    } else {
                        $('#favoriteStatusText').text('加到追蹤清單');
                        $('#favoriteStatus').removeClass('google-icon-fill').addClass('google-icon');
                    }
                }
            });
        };

        $(function () {
            $("#favorite").click(function () {
                addFavorite({{ $lot->id }});
            });
        });
    </script>
@endpush
