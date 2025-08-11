@extends('layouts.member')

@inject('carbonPresenter', 'App\Presenters\CarbonPresenter')
@push('scripts')
    <script>
        (function(){
            function setVH(){
                var h = (window.visualViewport ? window.visualViewport.height : window.innerHeight);
                var vh = h * 0.01;
                document.documentElement.style.setProperty('--vh', vh + 'px');
            }
            // Run on DOM ready and also after full load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setVH, { once: true });
            } else { setVH(); }
            window.addEventListener('load', setVH, { once: true });
            // Update only on orientationchange (debounced), not on resize or visualViewport
            window.addEventListener('orientationchange', function(){
                // debounce to let the browser settle after rotation
                setTimeout(setVH, 200);
            });
        })();
    </script>
@endpush

@section('sub-content')
    <!-- Hero Banner Section -->
    <div class="hero-banner-section uk-position-relative uk-visible-toggle uk-light uk-visible@l" tabindex="-1" uk-slideshow="autoplay: true; animation: fade; autoplay-interval: 5000">
        <ul class="uk-slideshow-items" uk-height-viewport>
            @if($banners->count() != 0)
                @foreach($banners as $banner)
                    <li>
                        <a href="{{ $banner->link }}" class="hero-banner-link">
                            <img src="{{ $banner->desktop_banner->url }}" alt="{{ $banner->slogan }}" uk-cover>
                            @if($banner->slogan != '')
                                <div class="uk-position-bottom-center uk-position-medium uk-text-center uk-light hero-content">
                                    <div class="hero-text-container">
                                        <h2 class="hero-title uk-margin-remove">{{ $banner->slogan }}</h2>
                                        <div class="hero-divider"></div>
                                        <div class="hero-cta">
                                            <span class="hero-cta-text">探索更多</span>
                                            <span class="material-symbols-outlined hero-cta-icon">arrow_forward</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
        <a class="uk-position-center-left uk-position-small uk-hidden-hover hero-nav-btn" uk-slidenav-previous
           uk-slideshow-item="previous"></a>
        <a class="uk-position-center-right uk-position-small uk-hidden-hover hero-nav-btn" uk-slidenav-next
           uk-slideshow-item="next"></a>

        <!-- Hero Indicators -->
        <div class="uk-position-bottom-center uk-position-small">
            <ul class="uk-dotnav uk-dotnav-contrast hero-indicators">
                @foreach($banners as $index => $banner)
                    <li uk-slideshow-item="{{ $index }}">
                        <a href="#"></a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Mobile Hero Banner -->
    <div class="uk-position-relative uk-visible-toggle uk-light uk-hidden@l mobile-hero" tabindex="-1" uk-slideshow="autoplay: false; animation: slide;">
        <ul class="uk-slideshow-items my-element">
            @foreach($banners as $banner)
                <li>
                    <a href="{{ $banner->link }}" class="mobile-hero-link">
                        <img src="{{ $banner->mobile_banner->url }}" alt="{{ $banner->slogan }}" uk-cover>
                        <div class="uk-position-bottom uk-position-bottom uk-text-center uk-light mobile-hero-content">
                            <div class="mobile-hero-text-container">
                                <h3 class="mobile-hero-title uk-margin-remove">{{ $banner->slogan }}</h3>
                                <div class="mobile-hero-divider"></div>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
        <a class="uk-position-center-left uk-position-small uk-hidden-hover mobile-hero-nav-btn" uk-slidenav-previous
           uk-slideshow-item="previous"></a>
        <a class="uk-position-center-right uk-position-small uk-hidden-hover mobile-hero-nav-btn" uk-slidenav-next
           uk-slideshow-item="next"></a>
    </div>
@endsection

@section('content')
    <!-- Main Content Container -->
    <div class="main-content-container">
        <!-- Featured Categories Section -->
    @if($productsByCategory && count($productsByCategory) > 0)
        @foreach($productsByCategory as $categoryData)
                <section class="category-section">
                    <div class="section-header">
                        <div class="section-title-container">
                            <h2 class="section-title">{{ $categoryData['category']->name }}</h2>
                            <div class="section-subtitle">精選商品</div>
                        </div>
                        <div class="section-decoration">
                            <div class="decoration-line"></div>
                            <div class="decoration-dot"></div>
                        </div>
                    </div>

                    <!-- Desktop Category Slider -->
            <div class="uk-visible@m">
                        <div class="uk-slider-container-offset modern-slider" uk-slider="finite: true; sets: true;">
                            <div class="uk-position-relative uk-visible-toggle" tabindex="-1">
                        <ul class="uk-slider-items uk-child-width-1-4@s uk-grid-match" uk-grid>
                            @foreach($categoryData['lots'] as $product)
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
                            @endforeach
                            <li>
                                        <div class="modern-card category-card main-category-card-click" main-categoryId="{{ $categoryData['category']->id }}">
                                            <div class="card-image-container">
                                                <img src="{{ $categoryData['category']->image->url ?? '/images/web/common/no-picture.jpg' }}" alt="{{ $categoryData['category']->name }}" class="card-image">
                                                <div class="card-overlay">
                                                    <div class="overlay-content">
                                                        <span class="material-symbols-outlined overlay-icon">category</span>
                                                        <span class="overlay-text">瀏覽分類</span>
                                                    </div>
                                                </div>
                                    </div>
                                            <div class="card-content">
                                                <h3 class="card-title">更多{{ $categoryData['category']->name }}的商品</h3>
                                                <div class="card-action">
                                                    <span class="action-text">查看更多</span>
                                                    <span class="material-symbols-outlined action-icon">arrow_forward</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
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
                        @endforeach
                        <li class="uk-width-5-6">
                            <div class="mobile-card main-category-card-click" main-categoryId="{{ $categoryData['category']->id }}">
                                <div class="mobile-card-image-container">
                                    <img src="{{ $categoryData['category']->image->url ?? '/images/web/common/no-picture.jpg' }}" alt="{{ $categoryData['category']->name }}" class="mobile-card-image">
                                    <div class="mobile-card-overlay">
                                        <div class="mobile-overlay-content">
                                            <span class="material-symbols-outlined mobile-overlay-icon">category</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mobile-card-content">
                                    <h3 class="mobile-card-title">更多{{ $categoryData['category']->name }}的商品</h3>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
                </section>
        @endforeach
    @endif

        <!-- Active Auctions Section -->
    @if($auctions->where('status', 1)->count() != 0)
            <section class="auction-section">
                <div class="section-header">
                    <div class="section-title-container">
                        <h2 class="section-title">進行中的拍賣會</h2>
                        <div class="section-subtitle">Live Auctions</div>
                    </div>
                    <div class="section-decoration">
                        <div class="decoration-line"></div>
                        <div class="decoration-dot"></div>
                    </div>
                </div>

                <!-- Desktop Auction Slider -->
        <div class="uk-visible@m">
                    <div class="uk-slider-container-offset modern-slider" uk-slider="finite: true">
                        <div class="uk-position-relative uk-visible-toggle" tabindex="-1">
                    <ul class="uk-slider-items uk-child-width-1-4@s uk-grid">
                        @foreach($auctions->where('status', 1) as $auction)
                            <li>
                                        <div class="modern-card auction-card auction-card-click" auctionId="{{ $auction->id }}">
                                            <div class="card-image-container">
                                                <img src="{{ $auction->lots->first()->blImages->first()->url }}" alt="{{ $auction->name }}" class="card-image">
                                                <div class="card-overlay">
                                                    <div class="overlay-content">
                                                        <span class="material-symbols-outlined overlay-icon">gavel</span>
                                                        <span class="overlay-text">參與競標</span>
                                                    </div>
                                                </div>
                                                <div class="auction-badge">
                                                    <span class="badge-text">進行中</span>
                                                </div>
                                            </div>
                                            <div class="card-content">
                                                <h3 class="card-title">{{ $auction->name }}</h3>
                                                <div class="auction-time">
                                                    <span class="material-symbols-outlined time-icon">schedule</span>
                                                    <span class="time-text">{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->end_at) }}</span>
                                    </div>
                                    </div>
                                </div>
                            </li>
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

                <!-- Mobile Auction Slider -->
        <div class="uk-hidden@m">
                    <div class="uk-position-relative uk-visible-toggle" tabindex="-1" uk-slider="center: true">
                <ul class="uk-slider-items uk-grid uk-grid-small uk-grid-match" uk-height-viewport="offset-top: true; offset-bottom: 30">
                    @foreach($auctions as $auction)
                        <li class="uk-width-5-6">
                                    <div class="mobile-card auction-card auction-card-click" auctionId="{{ $auction->id }}">
                                        <div class="mobile-card-image-container">
                                            <img src="{{ $auction->lots->first()->blImages->first()->url }}" alt="{{ $auction->name }}" class="mobile-card-image">
                                            <div class="mobile-card-overlay">
                                                <div class="mobile-overlay-content">
                                                    <span class="material-symbols-outlined mobile-overlay-icon">gavel</span>
                                                </div>
                                            </div>
                                            <div class="mobile-auction-badge">
                                                <span class="mobile-badge-text">進行中</span>
                                            </div>
                                    </div>
                                        <div class="mobile-card-content">
                                            <h3 class="mobile-card-title">{{ $auction->name }}</h3>
                                            <div class="mobile-auction-time">
                                                <span class="material-symbols-outlined mobile-time-icon">schedule</span>
                                                <span class="mobile-time-text">{{ $carbonPresenter->auctionPresent($auction->start_at, $auction->end_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
            </section>
    @endif
    </div>
@endsection

@push('style')
    <style>
        /* Hero Banner Styles */
        .hero-banner-section {
            margin-bottom: 0;
            border-radius: 0 0 20px 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .hero-banner-link {
            display: block;
            text-decoration: none;
            color: inherit;
        }

        .hero-content {
            background: linear-gradient(135deg, rgba(0, 58, 108, 0.6) 0%, rgba(0, 68, 128, 0.2) 100%);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 80px;
            z-index: 5;
        }

        .hero-text-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-divider {
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #f77f00, #d62828);
            margin: 20px auto;
            border-radius: 2px;
        }

        .hero-cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .hero-cta:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .hero-cta-text {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .hero-cta-icon {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .hero-cta:hover .hero-cta-icon {
            transform: translateX(5px);
        }

        .hero-nav-btn {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .hero-nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .hero-indicators {
            margin-bottom: 30px;
            z-index: 10;
        }

        .hero-indicators .uk-dotnav > li > a {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            width: 12px;
            height: 12px;
            transition: all 0.3s ease;
        }

        .hero-indicators .uk-dotnav > li.uk-active > a {
            background: #f77f00;
            transform: scale(1.2);
        }

        /* Mobile Hero Styles */
        .mobile-hero {
            margin-bottom: 0;
            border-radius: 0 0 15px 15px;
            overflow: hidden;
        }

        .mobile-hero-link {
            display: block;
            text-decoration: none;
            color: inherit;
        }

        .mobile-hero-content {
            background: linear-gradient(135deg, rgba(0, 58, 108, 0.6) 0%, rgba(0, 68, 128, 0.4) 100%);
            backdrop-filter: blur(10px);
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }

        .mobile-hero-text-container {
            max-width: 100%;
        }

        .mobile-hero-title {
            font-size: 1.5rem;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            margin-bottom: 10px;
        }

        .mobile-hero-divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #f77f00, #d62828);
            margin: 10px auto;
            border-radius: 2px;
        }

        .mobile-hero-nav-btn {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main Content Container */
        .main-content-container {
            padding: 60px 0;
            background: #fff;
        }

        /* Section Styles */
        .category-section,
        .auction-section {
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

        .card-price {
            margin-top: auto;
            text-align: center;
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

        .card-action {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #003a6c, #004480);
            color: white;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .card-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 58, 108, 0.3);
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

        .auction-time {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .time-icon {
            font-size: 1rem;
            color: #f77f00;
        }

        /* Modern Slider Styles */
        .modern-slider {
            margin: 0 -15px;
        }

        .slider-nav-btn {
            background: rgba(0, 58, 108, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: white;
        }

        .slider-nav-btn:hover {
            background: rgba(0, 58, 108, 1);
            transform: scale(1.1);
        }

        .slider-dots .uk-dotnav > li > a {
            background: rgba(0, 58, 108, 0.3);
            border-radius: 50%;
            width: 10px;
            height: 10px;
            transition: all 0.3s ease;
        }

        .slider-dots .uk-dotnav > li.uk-active > a {
            background: #003a6c;
            transform: scale(1.2);
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

        /* Responsive Adjustments */
        @media (max-width: 959px) {
            .hero-title {
                font-size: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .main-content-container {
                padding: 40px 0;
            }

            .category-section,
            .auction-section {
                margin-bottom: 60px;
            }
        }

        @media (max-width: 639px) {
            .hero-title {
                font-size: 1.5rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .main-content-container {
                padding: 30px 0;
            }

            .category-section,
            .auction-section {
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

        /* Ensure elements stay visible after animation */
        .animated {
            opacity: 1 !important;
            transform: none !important;
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

        .slide-in-left {
            animation: slideInLeft 0.8s ease-in-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .slide-in-right {
            animation: slideInRight 0.8s ease-in-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Allow mobile card shadows to render outside slider bounds */
        @media (max-width: 959px) {
            /* UIkit creates a .uk-slider-container around .uk-slider-items with overflow:hidden by default */
            .uk-slider-container {
                overflow-x: clip; /* prevent horizontal scroll caused by grid negative margins and wide slides */
                overflow-y: visible; /* keep shadows and overlays visible vertically */
            }
            .uk-slider-items { overflow: visible; }
            .uk-slider-items.uk-grid {
                margin-left: 0 !important; /* avoid extra width from grid negative margin */
                padding-left: 10px; /* keep first card from touching the edge */
                padding-right: 10px; /* keep last card from touching the edge */
            }
            .uk-slider-items > li { overflow: visible; padding-bottom: 16px; }
            /* Ensure the card paints above neighbors so the shadow isn't hidden */
            .uk-slider-items > li .mobile-card { position: relative; z-index: 1; }
            .uk-slider-items > li .mobile-card:hover { z-index: 2; }
        }

        /* Ensure mobile hero slides use a stable viewport height to prevent gap growth on scroll */
        .mobile-hero .uk-slideshow-items > li {
            height: 100vh; /* fallback for browsers without svh */
        }
        @supports (height: 100svh) {
            .mobile-hero .uk-slideshow-items > li {
                height: 100svh; /* small viewport height is stable as the URL bar shows/hides */
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function () {
            // Click handlers
            $('.auction-card-click').on('click', function() {
                let auctionId = $(this).attr('auctionId');
                window.location.assign('/auctions/'+auctionId);
            });

            $('.product-card-click').on('click', function() {
                let productId = $(this).attr('productId');
                window.location.assign('/products/'+productId);
            });

            $('.main-category-card-click').on('click', function() {
                let mainCategoryId = $(this).attr('main-categoryId');
                window.location.assign('/m-categories/'+ mainCategoryId);
            });

            // Optional hover state toggle (no animations)
            $('.modern-card, .mobile-card').on('mouseenter', function() {
                $(this).addClass('card-hover');
            }).on('mouseleave', function() {
                $(this).removeClass('card-hover');
            });

            // Smooth scroll for in-page anchors (no reveal animations)
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 800);
                }
            });
        });
    </script>
@endpush
