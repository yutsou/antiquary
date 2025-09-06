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

        <!-- Knowledge Article Section -->
        <section class="">
            <div class="section-header">
                <div class="section-title-container">
                    <h2 class="section-title">古董知識</h2>
                    <div class="section-subtitle">Antique Knowledge</div>
                </div>
                <div class="section-decoration">
                    <div class="decoration-line"></div>
                    <div class="decoration-dot"></div>
                </div>
            </div>

            <div class="article-container">
                <div class="article-card">
                    <div class="article-header">
                        <div class="article-icon">
                            <span class="material-symbols-outlined">auto_stories</span>
                        </div>
                        <div class="article-meta">
                            <h3 class="article-title" style="color: #fff;">歐洲瓷盒的歷史與原始用途</h3>
                            <div class="article-subtitle">European Porcelain Boxes: History & Original Purposes</div>
                        </div>
                    </div>

                    <div class="article-content">
                        <div class="article-intro">
                            <p>歐洲瓷盒原始功能兼具實用與裝飾，從18世紀的鼻菸盒、香粉盒，到19–20世紀的珠寶飾品盒，始終象徵着貴族與上層社會的生活美學。今天，它們更多被視為藝術品與收藏品，每件手工繪製的瓷盒，都是承載歐洲工藝與浪漫風格的迷你藝術品。</p>
                        </div>

                        <div class="article-sections">
                            <div class="article-section">
                                <div class="section-number">1️⃣</div>
                                <div class="section-content">
                                    <h4 class="section-title">起源與發展</h4>
                                    <p>18世紀的法國（尤其是 Limoges 與巴黎工坊）是瓷盒的發源地之一。這些瓷盒在當時多為手工製作、手繪裝飾，並鑲上黃銅、鎏金或銀質的金屬邊框。最初稱為 "bonbonnière"，是上流社會貴族男女隨身攜帶的精緻小物。</p>
                                </div>
                            </div>

                            <div class="article-section">
                                <div class="section-number">2️⃣</div>
                                <div class="section-content">
                                    <h4 class="section-title">主要用途</h4>
                                    <ul class="usage-list">
                                        <li><strong>珠寶與飾品盒：</strong>最常見的功能，用來存放戒指、耳環、胸針或小件首飾，特別放在梳妝台上，兼具實用與裝飾。</li>
                                        <li><strong>鼻菸盒（snuff box）：</strong>18–19世紀盛行於貴族圈，用來存放磨成粉末的鼻菸。當時瓷質鼻菸盒象徵身份與品味。</li>
                                        <li><strong>香粉盒與化妝小盒：</strong>部分瓷盒設計較扁平，內部可能放置蜜粉或香料，隨身攜帶。</li>
                                        <li><strong>藥盒：</strong>小型瓷盒有時也用於裝藥丸，方便貴婦或紳士外出時使用。</li>
                                        <li><strong>禮物與收藏：</strong>手工繪製的瓷盒經常被製作為禮物，象徵祝福與愛情（尤其是心型、花卉彩繪的款式）。</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="article-section">
                                <div class="section-number">3️⃣</div>
                                <div class="section-content">
                                    <h4 class="section-title">裝飾特色</h4>
                                    <ul class="decoration-list">
                                        <li><strong>手繪花卉與金彩：</strong>玫瑰、牡丹、矢車菊、百合等花卉是最常見的題材，代表愛情、優雅與浪漫。</li>
                                        <li><strong>金屬鑲邊與鎖扣：</strong>多為黃銅或鎏金，鎖扣上常見蝴蝶、花朵或葉片圖案。</li>
                                        <li><strong>獨特造型：</strong>橢圓形、心型、圓形、八角形等，象徵不同寓意。心型常與愛情、婚禮、紀念日相關。</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="article-section">
                                <div class="section-number">4️⃣</div>
                                <div class="section-content">
                                    <h4 class="section-title">英國與法國的差異</h4>
                                    <div class="comparison-grid">
                                        <div class="comparison-item">
                                            <h5>法國（Limoges）</h5>
                                            <p>以白瓷為基底，強調手繪精細度與柔美彩繪，代表高端瓷藝傳統。</p>
                                        </div>
                                        <div class="comparison-item">
                                            <h5>英國瓷盒</h5>
                                            <p>如 Staffordshire, Royal Worcester, Royal Crown Derby 等工坊出品，更注重工整描金與圖案設計，部分走浪漫或鄉村風格。</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="article-section">
                                <div class="section-number">5️⃣</div>
                                <div class="section-content">
                                    <h4 class="section-title">今日的收藏價值</h4>
                                    <p>古董瓷盒因小巧、容易收集，深受歐洲與美國藏家喜愛，常成系列收藏（如「心型系列」、「花卉系列」、「Limoges 系列」）。除了作為飾品收納盒，許多收藏家也將它們陳列於玻璃櫃中，當作室內點綴與談資。</p>
                                </div>
                            </div>
                        </div>

                        <div class="article-footer">
                            <div class="article-tags">
                                <span class="tag">歐洲古董</span>
                                <span class="tag">瓷盒</span>
                                <span class="tag">收藏知識</span>
                                <span class="tag">工藝歷史</span>
                            </div>
                            <div class="article-cta">
                                <button class="read-more-btn">
                                    <span>探索更多古董知識</span>
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('css/hero.css') }}">
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

            $('.read-more-btn').on('click', function() {
                // 這裡可以導向到知識文章列表頁面或相關頁面
                // 暫時使用 alert 提示，您可以根據實際需求修改
                alert('即將推出更多古董知識文章！');
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
