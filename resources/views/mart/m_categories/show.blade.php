@extends('layouts.member')
@inject('carbonPresenter', 'App\Presenters\CarbonPresenter')

@section('content')
    <!-- Breadcrumb -->
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>

    <!-- Main Content Container -->
    <div class="main-content-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="section-title-container uk-text-left">
                <h1 class="section-title">{{ $head }}</h1>
            </div>
        </div>

        <!-- Sub Categories Section -->
        <section class="sub-categories-section">
            <div class="section-header">
                <div class="section-title-container">
                    <h2 class="section-title">子分類</h2>
                    <div class="section-subtitle">Sub Categories</div>
                </div>
                <div class="section-decoration">
                    <div class="decoration-line"></div>
                    <div class="decoration-dot"></div>
                </div>
            </div>

            <!-- Desktop Sub Categories -->
            <div class="uk-visible@m">
                <div class="uk-child-width-1-4@s uk-grid-small uk-grid-match" uk-grid>
                @foreach($sCategories as $sCategory)
                    <div>
                            <div class="modern-card sub-category-card sub-category-click"
                                 style="background: linear-gradient(135deg, {{ $mCategory->color_hex }}, {{ $mCategory->color_hex }}dd);"
                                 data-href="{{ route('mart.s_categories.show', [$mCategory, $sCategory]) }}">
                                <div class="card-content">
                                    <h3 class="card-title" style="color: white;">{{ $sCategory->name }}</h3>
                                    <div class="card-action">
                                        <span class="action-text" style="color: white;">瀏覽分類</span>
                                        <span class="material-symbols-outlined action-icon" style="color: white;">arrow_forward</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Mobile Sub Categories -->
            <div class="uk-hidden@m">
                <div class="uk-child-width-1-2@s uk-grid-small uk-grid-match" uk-grid>
                    @foreach($sCategories as $sCategory)
                        <div>
                            <div class="mobile-card sub-category-card sub-category-click"
                                 style="background: linear-gradient(135deg, {{ $mCategory->color_hex }}, {{ $mCategory->color_hex }}dd);"
                                 data-href="{{ route('mart.s_categories.show', [$mCategory, $sCategory]) }}">
                                <div class="mobile-card-content">
                                    <h3 class="mobile-card-title" style="color: white;">{{ $sCategory->name }}</h3>
                                </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </section>

                @php
                    $auctionLotsInPage = $lots->where('auction_end_at', '!=', null);
                    $directLotsInPage = $lots->where('auction_end_at', null);
                @endphp

                <!-- 拍賣商品區塊 -->
                @if($auctionLotsInPage->count() > 0)
            <section class="lots-section">
                <div class="section-header">
                    <div class="section-title-container">
                        <h2 class="section-title">拍賣商品</h2>
                        <div class="section-subtitle">Live Auctions</div>
                    </div>
                    <div class="section-decoration">
                        <div class="decoration-line"></div>
                        <div class="decoration-dot"></div>
                    </div>
                </div>

                <!-- Desktop Auction Lots -->
                <div class="uk-visible@m">
                    <div class="uk-child-width-1-3@s uk-child-width-1-4@m uk-grid-small uk-grid-match" uk-grid>
                        @foreach($auctionLotsInPage as $singleLot)
                            <div>
                                <div class="modern-card auction-lot-card bidding-card-click" lotId="{{ $singleLot->id }}">
                                    <div class="card-image-container">
                                        <img src="{{ $singleLot->blImages->first()->url }}" alt="{{ $singleLot->name }}" class="card-image">
                                        <div class="card-overlay">
                                            <div class="overlay-content">
                                                <span class="material-symbols-outlined overlay-icon">gavel</span>
                                                <span class="overlay-text">參與競標</span>
                                            </div>
                                        </div>
                                        <div class="auction-badge">
                                            <span class="badge-text">拍賣中</span>
                                        </div>
                                        <div class="favorite-container">
                                            @include('mart.components.favorite-inline', $singleLot)
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">{{ $singleLot->name }}</h3>
                                        <div class="card-price">
                                            <span class="price-label">目前出價</span>
                                            <span class="price-value" id="lot-{{ $singleLot->id }}-price">NT${{ number_format($singleLot->current_bid) }}</span>
                                        </div>
                                        <div class="auction-time">
                                            <span class="material-symbols-outlined time-icon">schedule</span>
                                            <span class="time-text">{!! $carbonPresenter->lotPresent($singleLot->id, $singleLot->auction_end_at) !!}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Mobile Auction Lots -->
                <div class="uk-hidden@m">
                    <div class="uk-child-width-1-2@s uk-grid-small uk-grid-match" uk-grid>
                        @foreach($auctionLotsInPage as $singleLot)
                            <div>
                                <div class="mobile-card auction-lot-card bidding-card-click" lotId="{{ $singleLot->id }}">
                                    <div class="mobile-card-image-container">
                                        <img src="{{ $singleLot->blImages->first()->url }}" alt="{{ $singleLot->name }}" class="mobile-card-image">
                                        <div class="mobile-card-overlay">
                                            <div class="mobile-overlay-content">
                                                <span class="material-symbols-outlined mobile-overlay-icon">gavel</span>
                                            </div>
                                        </div>
                                        <div class="mobile-auction-badge">
                                            <span class="mobile-badge-text">拍賣中</span>
                                        </div>
                                        <div class="mobile-favorite-container">
                                            @include('mart.components.favorite-inline', $singleLot)
                                        </div>
                                    </div>
                                    <div class="mobile-card-content">
                                        <h3 class="mobile-card-title">{{ $singleLot->name }}</h3>
                                        <div class="mobile-auction-time">
                                            <span class="material-symbols-outlined mobile-time-icon">schedule</span>
                                            <span class="mobile-time-text">{!! $carbonPresenter->lotPresent($singleLot->id, $singleLot->auction_end_at) !!}</span>
                                        </div>
                                    </div>
                                    <div class="mobile-card-price-container">
                                        <div class="mobile-card-price">
                                            <span class="mobile-price-label">目前出價</span>
                                            <span class="mobile-price-value" id="lot-{{ $singleLot->id }}-price">NT${{ number_format($singleLot->current_bid) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
                @endif

                <!-- 商店直賣商品區塊 -->
                @if($directLotsInPage->count() > 0)
            <section class="lots-section">
                <div class="section-header">
                    <div class="section-title-container">
                        <h2 class="section-title">Antiquary 精選</h2>
                        <div class="section-subtitle">Featured Products</div>
                    </div>
                    <div class="section-decoration">
                        <div class="decoration-line"></div>
                        <div class="decoration-dot"></div>
                    </div>
                </div>

                <!-- Desktop Direct Lots -->
                <div class="uk-visible@m">
                    <div class="uk-child-width-1-3@s uk-child-width-1-4@m uk-grid-small uk-grid-match" uk-grid>
                        @foreach($directLotsInPage as $singleLot)
                            <div>
                                <div class="modern-card product-lot-card antiquary-card-click" lotId="{{ $singleLot->id }}">
                                    <div class="card-image-container">
                                        <img src="{{ $singleLot->blImages->first()->url }}" alt="{{ $singleLot->name }}" class="card-image">
                                        <div class="card-overlay">
                                            <div class="overlay-content">
                                                <span class="material-symbols-outlined overlay-icon">visibility</span>
                                                <span class="overlay-text">查看詳情</span>
                                            </div>
                                        </div>
                                        <div class="favorite-container">
                                            @include('mart.components.favorite-inline', $singleLot)
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">{{ $singleLot->name }}</h3>
                                        <div class="card-price">
                                            <span class="price-label">售價</span>
                                            <span class="price-value" id="lot-{{ $singleLot->id }}-price">NT${{ number_format($singleLot->current_bid) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Mobile Direct Lots -->
                <div class="uk-hidden@m">
                    <div class="uk-child-width-1-2@s uk-grid-small uk-grid-match" uk-grid>
                        @foreach($directLotsInPage as $singleLot)
                            <div>
                                <div class="mobile-card product-lot-card antiquary-card-click" lotId="{{ $singleLot->id }}">
                                    <div class="mobile-card-image-container">
                                        <img src="{{ $singleLot->blImages->first()->url }}" alt="{{ $singleLot->name }}" class="mobile-card-image">
                                        <div class="mobile-card-overlay">
                                            <div class="mobile-overlay-content">
                                                <span class="material-symbols-outlined mobile-overlay-icon">visibility</span>
                                            </div>
                                        </div>
                                        <div class="mobile-favorite-container">
                                            @include('mart.components.favorite-inline', $singleLot)
                                        </div>
                                    </div>
                                    <div class="mobile-card-content">
                                        <h3 class="mobile-card-title">{{ $singleLot->name }}</h3>
                                    </div>
                                    <div class="mobile-card-price-container">
                                        <div class="mobile-card-price">
                                            <span class="mobile-price-label">售價</span>
                                            <span class="mobile-price-value" id="lot-{{ $singleLot->id }}-price">NT${{ number_format($singleLot->current_bid) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
                @endif

                <!-- 分頁導航 -->
                @if(isset($paginator) && $paginator->hasPages())
            <div class="pagination-section">
                        <ul class="uk-pagination uk-flex-center" uk-margin>
                            {{-- 上一頁 --}}
                            @if($paginator->onFirstPage())
                                <li><a href="#" class="uk-disabled"><span uk-pagination-previous></span></a></li>
                            @else
                                <li><a href="{{ $paginator->previousPageUrl() }}"><span uk-pagination-previous></span></a></li>
                            @endif

                            {{-- 頁碼 --}}
                            @php
                                $start = max(1, $paginator->currentPage() - 2);
                                $end = min($paginator->lastPage(), $paginator->currentPage() + 2);

                                // 確保顯示至少5個頁碼
                                if ($end - $start < 4) {
                                    if ($start == 1) {
                                        $end = min($paginator->lastPage(), $start + 4);
                                    } else {
                                        $start = max(1, $end - 4);
                                    }
                                }
                            @endphp

                            {{-- 第一頁 --}}
                            @if($start > 1)
                                <li><a href="{{ $paginator->url(1) }}">1</a></li>
                                @if($start > 2)
                                    <li><span>...</span></li>
                                @endif
                            @endif

                            {{-- 頁碼範圍 --}}
                            @for($page = $start; $page <= $end; $page++)
                                @if($page == $paginator->currentPage())
                                    <li class="uk-active"><span>{{ $page }}</span></li>
                                @else
                                    <li><a href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                                @endif
                            @endfor

                            {{-- 最後一頁 --}}
                            @if($end < $paginator->lastPage())
                                @if($end < $paginator->lastPage() - 1)
                                    <li><span>...</span></li>
                                @endif
                                <li><a href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a></li>
                            @endif

                            {{-- 下一頁 --}}
                            @if($paginator->hasMorePages())
                                <li><a href="{{ $paginator->nextPageUrl() }}"><span uk-pagination-next></span></a></li>
                            @else
                                <li><a href="#" class="uk-disabled"><span uk-pagination-next></span></a></li>
                            @endif
                        </ul>

                        {{-- 顯示總數信息 --}}
                <div class="pagination-info">
                            顯示第 {{ ($paginator->currentPage() - 1) * $paginator->perPage() + 1 }} - {{ min($paginator->currentPage() * $paginator->perPage(), $paginator->total()) }} 項，共 {{ $paginator->total() }} 項商品
                        </div>
                    </div>
                @endif
            </div>

    @include('mart.components.favorite-outline')
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
    <style>
        /* Main Content Container */
        .main-content-container {
            padding: 60px 0;
            background: #fff;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        /* Section Styles */
        .sub-categories-section,
        .lots-section {
            margin-bottom: 80px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .section-title-container {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #003a6c;
            margin: 0 0 10px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .section-decoration {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .decoration-line {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #003a6c, #f77f00);
            border-radius: 2px;
        }

        .decoration-dot {
            width: 8px;
            height: 8px;
            background: #d62828;
            border-radius: 50%;
        }

        /* Modern Card Styles */
        .modern-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .modern-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-image-container {
            position: relative;
            overflow: hidden;
            aspect-ratio: 4/3;
        }

        .card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .modern-card:hover .card-image {
            transform: scale(1.1);
        }

        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 58, 108, 0.8) 0%, rgba(247, 127, 0, 0.8) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modern-card:hover .card-overlay {
            opacity: 1;
        }

        .overlay-content {
            text-align: center;
            color: white;
        }

        .overlay-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .overlay-text {
            font-size: 1rem;
            font-weight: 600;
        }

        .card-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #003a6c !important;
            margin: 0 0 15px 0;
            line-height: 1.4;
            min-height: 2.8em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-wrap: break-word;
            word-break: break-word;
            text-shadow: none;
            background: transparent;
        }

        /* Sub category card title - white color */
        .sub-category-card .card-title {
            color: white !important;
        }

        .card-price {
            margin-top: auto;
            text-align: center;
            margin-bottom: 15px;
        }

        .price-label {
            display: block;
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .price-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #d62828;
        }

        .auction-time {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .time-icon {
            font-size: 1rem;
            color: #f77f00;
        }

        .card-action {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .card-action:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .action-icon {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .card-action:hover .action-icon {
            transform: translateX(3px);
        }

        /* Auction Badge */
        .auction-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #d62828, #9e1b1b);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(214, 40, 40, 0.3);
        }

        .badge-text {
            color: white;
        }

        /* Favorite Container */
        .favorite-container {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 10;
        }

        /* Mobile Card Styles */
        .mobile-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .mobile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .mobile-card-image-container {
            position: relative;
            overflow: hidden;
            aspect-ratio: 16/9;
        }

        .mobile-card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .mobile-card:hover .mobile-card-image {
            transform: scale(1.05);
        }

        .mobile-card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 58, 108, 0.7) 0%, rgba(247, 127, 0, 0.7) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .mobile-card:hover .mobile-card-overlay {
            opacity: 1;
        }

        .mobile-overlay-content {
            text-align: center;
            color: white;
        }

        .mobile-overlay-icon {
            font-size: 1.5rem;
        }

        .mobile-card-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .mobile-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #003a6c !important;
            margin: 0 0 10px 0;
            line-height: 1.3;
            min-height: 2.6em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-wrap: break-word;
            word-break: break-word;
            text-shadow: none;
            background: transparent;
        }

        .mobile-card-price-container {
            background: white;
            padding: 15px 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }

        .mobile-card-price {
            text-align: center;
        }

        .mobile-price-label {
            display: block;
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 3px;
        }

        .mobile-price-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #d62828;
        }

        .mobile-auction-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #d62828, #9e1b1b);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .mobile-badge-text {
            color: white;
        }

        .mobile-auction-time {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: auto;
        }

        .mobile-time-icon {
            font-size: 0.9rem;
            color: #f77f00;
        }

        .mobile-favorite-container {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
        }

        /* Pagination Styles */
        .pagination-section {
            margin-top: 60px;
            text-align: center;
        }

        .pagination-info {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* Responsive Adjustments */
        @media (max-width: 959px) {
            .section-title {
                font-size: 2rem;
            }

            .main-content-container {
                padding: 40px 0;
            }

            .sub-categories-section,
            .lots-section {
                margin-bottom: 60px;
            }
        }

        @media (max-width: 639px) {
            .section-title {
                font-size: 1.5rem;
            }

            .main-content-container {
                padding: 30px 0;
            }

            .sub-categories-section,
            .lots-section {
                margin-bottom: 80px;
            }

            .card-content {
                padding: 20px;
            }

            .mobile-card-content {
                padding: 15px;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 1.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }

        .slide-up {
            animation: slideUp 2.0s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }

        .scale-in {
            animation: scaleIn 1.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.85);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
        $(function () {
            $(".bidding-card-click").click(function(e){
                // 如果點擊的是愛心元素或其子元素，不執行卡片點擊事件
                if ($(e.target).closest('.favorite, .un-login-favorite').length > 0) {
                    return;
                }

                let lotId = $(this).attr('lotId');
                let url = '{{ route("mart.lots.show", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
            });
        });
        $(function () {
            $(".antiquary-card-click").click(function(e){
                // 如果點擊的是愛心元素或其子元素，不執行卡片點擊事件
                if ($(e.target).closest('.favorite, .un-login-favorite').length > 0) {
                    return;
                }

                let lotId = $(this).attr('lotId');
                let url = '{{ route("mart.products.show", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
            });
        });
        $(function () {
            $(".sub-category-click").click(function(e){
                let href = $(this).attr('data-href');
                window.location.assign(href);
            });
        });
    </script>

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

        Echo.channel(`lotCard`)
            .listen('FreshLotCardPrice', (e) => {
                let lotPrice = $('#lot-'+e.lotId+'-price');
                let bid = e.bid;
                lotPrice.text('NT$'+number_format(bid));
            });
        Echo.channel(`lotCard`)
            .listen('FreshLotCardTime', (e) => {
                let countdown = $('#countdown-'+e.lotId);
                countdown.attr('end-at', e.dueTime);
            });

        let setLotCardCountdown = function(countdown){
            const second = 1000,
                minute = second * 60,
                hour = minute * 60,
                day = hour * 24;

            function freshCountdown(countdown, dueTime){
                let now = new Date().getTime();

                let distance = dueTime - now;
                let days = Math.floor(distance / (day)).toString().padStart(2, '0');
                let hours = Math.floor((distance % (day)) / (hour)).toString().padStart(2, '0');
                let minutes = Math.floor((distance % (hour)) / (minute)).toString().padStart(2, '0');
                let seconds = Math.floor((distance % (minute)) / second).toString().padStart(2, '0');


                //do something later when date is reached
                if (distance < 1000) {
                    clearInterval(timer);
                    countdown.text('競標結束')
                } else {
                    if(distance > 86400000) {
                        countdown.text('於 '+days+ '天內結束競標')
                    } else {
                        countdown.text('於 '+hours+ '時'+minutes+'分'+seconds+'秒 後結束')
                    }
                }
            }

            let timer = setInterval(function() {
                let dueTimeIso = countdown.attr('end-at');
                let dueTime = new Date(dueTimeIso).getTime();
                freshCountdown(countdown, dueTime)
            }, 500)
        };

        $(function () {
            lotCardCountdowns = $('.lot-card-countdowns');

            lotCardCountdowns.each(function () {
                let lotCardCountdown = $('#'+this.id);
                setLotCardCountdown(lotCardCountdown);
            });
        });
    </script>
@endpush
