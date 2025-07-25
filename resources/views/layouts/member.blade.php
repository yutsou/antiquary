<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>{{ $title }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('/images/web/common/icon.png') }}">
        <!-- {{ $version = '35' }} -->
        <!-- Meta -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('extensions/uikit-3.15.19/css/uikit.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{$version}}" />
        <link rel="stylesheet" href="{{ asset('extensions/sweetalert2/css/material-ui.min.css') }}" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        @stack('style')
        <style>
            .custom-category-card-click {
                cursor: pointer;
                -webkit-tap-highlight-color: transparent;
            }
        </style>

        <!-- Scripts -->

        <script src="{{ asset('extensions/uikit-3.15.19/js/uikit.min.js') }}"></script>
        <script src="{{ asset('extensions/uikit-3.15.19/js/uikit-icons.min.js') }}"></script>
        <script src="{{ asset('js/jquery-3.6.0/jquery.min.js') }}"></script>
        <script src="{{ asset('js/custom.js') }}?v={{$version}}"></script>
        <script src="{{ asset('extensions/sweetalert2/js/sweetalert2.min.js') }}"></script>

        <script>
            $(function () {
                $(".custom-category-card-click").click(function(){
                    let categoryId = $(this).attr('categoryId');
                    let url = '/m-categories/'+categoryId;
                    window.location.assign(url);
                });
            });
        </script>
        @stack('scripts')
    </head>
    <body>
        <div class="uk-visible@m">
            <div style="height:80px; background-color: #fff;" class="fixed-top">
                <div class="uk-container">
                    <nav class="uk-navbar-container" uk-navbar style="background-color: #fff;">
                        <div class="uk-navbar-left">
                            <ul class="uk-navbar-nav">
                                <a class="uk-navbar-item uk-logo" href="/" style="color: #003a6c; font-size: 1.2em"><img src="{{ asset('/images/web/common/logo.png') }}" alt="Antiquary" style="height: 40px"></a>
                                <li>
                                    <a href="#">分類</a>
                                    <div uk-dropdown class="uk-width-1-1">
                                        <div class="uk-child-width-1-5@s uk-text-center" uk-grid>
                                            @foreach($categories as $category)
                                                <div>
                                                    <div class="uk-card uk-card-default uk-card-small uk-card-hover uk-grid-collapse uk-margin custom-category-card-click" uk-grid
                                                         style="background-color: {{ $category->color_hex }};" categoryId="{{ $category->id }}">
                                                        <div class="uk-card-media-left uk-cover-container uk-width-1-3">
                                                            <img src="{{ $category->image->url }}" alt="" uk-cover>
                                                        </div>
                                                        <div class="uk-width-expand">
                                                            <div class="uk-card-body">
                                                                <h3 class="uk-card-title" style="color: #fff; ">{{ $category->name }}</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="uk-navbar-center" style="width: 60%;">
                            <form method="get" action="{{ route('mart.lots.search') }}" class="uk-navbar-item uk-width-expand" style="display: flex; gap: 0.5em;">
                                <input class="uk-input" type="text" name="q" placeholder="搜尋物品" style="flex: 1 1 0; min-width: 0;" />
                                <button class="uk-button custom-button-1" type="submit" style="white-space: nowrap;">搜尋</button>
                            </form>
                        </div>
                        <div class="uk-navbar-right">
                            @guest
                                <ul class="uk-navbar-nav">
                                    <li><a href="/login">登入</a></li>
                                    <li><a href="/register">註冊</a></li>
                                </ul>
                            @endguest
                            @auth
                                <ul class="uk-navbar-nav">
                                    <li>
                                        <a href="{{ route('account.cart.show') }}">
                                            <div id="cart-status" class="google-icon">
                                                <span class="material-symbols-outlined" uk-tooltip="title: 購物車; pos: top-left">shopping_cart</span>
                                                @if(Auth::user()->cart_count > 0)
                                                    <span class="uk-badge uk-text-middle" style="background-color: #d62828;" id="cart-count">{{ Auth::user()->cart_count }}</span>
                                                @endif
                                            </div>
                                        </a>
                                    </li>
                                    <li><a href="{{ route('account.unread_notices.index') }}">
                                            @if(Auth::user()->unreadNotices()->count() !== 0)
                                                <div id="notification-status" class="google-icon-fill">
                                                    <span class="material-symbols-outlined uk-text-middle">notifications</span>
                                                    <span class="uk-badge uk-text-middle" style="background-color: #d62828;" id="unread-notices-count">{{ Auth::user()->unreadNotices()->count() }}</span>
                                                </div>
                                            @else
                                                <div id="notification-status" class="google-icon">
                                                    <span class="material-symbols-outlined" uk-tooltip="title: 沒有未讀的通知; pos: top-left">notifications</span>
                                                </div>
                                            @endif
                                        </a></li>
                                </ul>
                                <ul class="uk-navbar-nav">
                                    <li>
                                        <a href="#">{{ Auth::user()->name }}</a>
                                        <div class="uk-navbar-dropdown" uk-dropdown="pos: bottom-left">
                                            <ul class="uk-nav uk-navbar-dropdown-nav">
                                                <li><a href="{{ route('dashboard') }}">會員中心</a></li>
                                                <hr>
                                                <li>
                                                    <form method="POST" action="{{ route('logout') }}">
                                                        @csrf
                                                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="custom-link">登出</a>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            @endauth
                        </div>
                    </nav>
                </div>
            </div>
        </div>
        <div class="uk-hidden@m">
            <div style="box-shadow: 0 8px 6px -6px #ced4da; background-color: #fff" class="fixed-top">
                <nav class="uk-container" uk-navbar>
                    <div class="uk-navbar-left">
                        <ul class="uk-navbar-nav">
                            <li>
                                <a href="/" style="color: #003a6c; font-size: 1.2em"><img src="{{ asset('/images/web/common/logo.png') }}" alt="Antiquary" style="height: 40px"></a>
                            </li>
                        </ul>
                    </div>
                    <div class="uk-navbar-right">
                        @guest
                            <ul class="uk-navbar-nav">
                                <li><a href="/login">登入</a></li>
                                <li><a href="/register">註冊</a></li>
                            </ul>
                        @endguest
                        @auth
                            <ul class="uk-navbar-nav">
                                <li>
                                    <a href="{{ route('account.cart.show') }}">
                                        <div id="cart-status" class="google-icon">
                                            <span class="material-symbols-outlined" uk-tooltip="title: 購物車; pos: top-left">shopping_cart</span>
                                            @if(Auth::user()->cart_count > 0)
                                                <span class="uk-badge uk-text-middle" style="background-color: #d62828;" id="cart-count">{{ Auth::user()->cart_count }}</span>
                                            @endif
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.unread_notices.index') }}">
                                        @if(Auth::user()->unreadNotices()->count() !== 0)
                                            <div id="notification-status" class="google-icon-fill">
                                                <span class="material-symbols-outlined uk-text-middle">notifications</span>
                                                <span class="uk-badge uk-text-middle" style="background-color: #d62828;" id="unread-notices-count">{{ Auth::user()->unreadNotices()->count() }}</span>
                                            </div>
                                        @else
                                            <div id="notification-status" class="google-icon">
                                                <span class="material-symbols-outlined" uk-tooltip="title: 沒有未讀的通知; pos: top-left">notifications</span>
                                            </div>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.favorites.index') }}">
                                        <span class="google-icon">
                                            <span class="material-symbols-outlined uk-text-middle">favorite</span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        @endauth
                        <a class="uk-navbar-toggle" uk-navbar-toggle-icon href="#toggle-animation" uk-toggle="target: #toggle-animation; animation: uk-animation-fade" style="margin-right: 10px;"></a>
                    </div>
                </nav>
                <div id="toggle-animation" class="uk-card uk-card-default uk-card-body" hidden>
                    <ul class="uk-nav-default" uk-nav>
                        <li class="uk-padding">
                            <form method="get" action="{{ route('mart.lots.search') }}">
                                <div class="uk-grid-small" uk-grid>
                                    <div class="uk-width-expand">
                                        <input class="uk-input" type="text" name="q" placeholder="搜尋物品">
                                    </div>
                                    <div class="uk-width-auto">
                                        <button class="uk-button custom-button-1">搜尋</button>
                                    </div>
                                </div>
                            </form>
                        </li>
                    </ul>

                    <ul class="uk-nav-default uk-nav-parent-icon uk-nav-left" uk-nav>
                        <li>
                            <a href="#">分類</a>
                            <div uk-dropdown class="uk-width-1-1" style="overflow: auto; height: 60vh;">
                                <div class="uk-child-width-1-5@s uk-text-center uk-grid-small" uk-grid>
                                    @foreach($categories as $category)
                                        <div>
                                            <div class="uk-card uk-card-default uk-card-small uk-card-hover uk-grid-collapse uk-margin custom-category-card-click" uk-grid
                                                 style="background-color: {{ $category->color_hex }};" categoryId="{{ $category->id }}">
                                                <div class="uk-card-media-left uk-cover-container uk-width-1-3">
                                                    <img src="{{ $category->image->url }}" alt="" uk-cover>
                                                </div>
                                                <div class="uk-width-expand">
                                                    <div class="uk-card-body">
                                                        <h3 class="uk-card-title" style="color: #fff; ">{{ $category->name }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </li>

                        @auth
                            <li>
                                <a href="#">{{ Auth::user()->name }}</a>
                                <ul class="uk-nav-sub">
                                    <li><a href="{{ route('dashboard') }}">會員中心</a></li>
                                    <li><hr></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="custom-link">登出</a>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endauth

                    </ul>

                </div>
            </div>
        </div>


        @yield('sub-content')

        <div class="uk-section" style="padding-top: 100px">
            <div class="uk-container">
                @yield('content')
            </div>
        </div>

        <hr>

        <div class="uk-section uk-transparent">
            <div class="uk-container">
                <div class="uk-grid-match uk-child-width-1-3@m uk-text-center" uk-grid>
                    <div>
                        <h4>關於 Antiquary</h4>
                        <ul class="uk-list uk-link-text">
                            <li><a href="{{ route('mart.about_antiquary.show') }}">關於我們</a></li>
                            <li><a href="{{ route('mart.antiquary_guaranty.show') }}">我們的保證</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4>關於委賣</h4>
                        <ul class="uk-list uk-link-text">
                            <li><a href="{{ route('mart.consignment_auction_notes.show') }}">委託拍賣須知</a></li>
                            <li><a href="{{ route('mart.consignment_auction_terms.show') }}">委託拍賣條款</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4>關於競標</h4>
                        <ul class="uk-list uk-link-text">
                            <li><a href="{{ route('mart.bidding-notes.show') }}">競標須知</a></li>
                            <li><a href="{{ route('mart.bidding-rules.show') }}">競標增額</a></li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="uk-margin-medium-top uk-text-center">
                    <a href="mailto:service@antiquary.com.tw" class="uk-link-text"><span uk-icon="mail"></span> service@antiquary.com.tw</a>
                </div>
                <div class="uk-margin uk-text-center">
                    <span><a href="{{ route('mart.terms.show') }}" class="uk-link-text">使用者條款</a> | <a href="{{ route('mart.privacy-policy.show') }}" class="uk-link-text">隱私政策</a></span>
                </div>
                <div class="uk-margin">
                    <div class="uk-text-center uk-child-width-1-1">© {{ now()->year }} Antiquary</div>
                </div>
            </div>
        </div>
    </body>
</html>
