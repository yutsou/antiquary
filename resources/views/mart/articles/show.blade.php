@extends('layouts.member')

@section('content')
    <!-- Back Button -->
    <div class="uk-margin-medium">
        <a href="/" class="read-more-btn custom-link-mute" style="display: inline-block; text-decoration: none;">
            <span class="material-symbols-outlined" style="margin-right: 8px;">arrow_back</span>
            <span>返回首頁</span>
        </a>
    </div>

    <!-- Article Container -->
    <div class="article-container">
        <div class="article-card">
            <!-- Article Header -->
            <div class="article-header">
                <div class="article-icon">
                    <span class="material-symbols-outlined">auto_stories</span>
                </div>
                <div class="article-meta">
                    <h1 class="article-title" style="color: #fff;">{{ $article->title }}</h1>
                    @if($article->subtitle)
                        <div class="article-subtitle">{{ $article->subtitle }}</div>
                    @endif
                </div>
            </div>

            <!-- Article Content -->
            <div class="article-content">
                <!-- Article Introduction -->
                <div class="article-intro">
                    <p>{{ $article->intro }}</p>
                </div>

                <!-- Article Sections -->
                <div class="article-sections">
                    <div class="article-section">
                        <div class="section-content">
                            <div class="article-full-content">
                                {!! nl2br(e($article->content)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Article Footer -->
                
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        /* 确保文章详情页面具有合适的样式 */
        .article-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .article-card {
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .article-full-content {
            line-height: 1.8;
            font-size: 16px;
        }
        
        .article-full-content p {
            margin-bottom: 15px;
        }
    </style>
@endpush
