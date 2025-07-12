@extends('layouts.auctioneer')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('auctioneer.dashboard') }}" class="custom-color-1 custom-link-mute">管理員中心</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>

    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>

            @if($requests->count() > 0)
                <div class="uk-margin">
                    @foreach($requests as $request)
                        <div class="uk-card uk-card-default uk-margin">
                            <div class="uk-card-body">
                                <div class="uk-grid-small" uk-grid>
                                    <div class="uk-width-expand">
                                        <h4 class="uk-card-title">
                                            <a href="{{ route('auctioneer.merge_shipping_requests.show', $request->id) }}" class="custom-link">
                                                合併運費請求 #{{ $request->id }}
                                            </a>
                                        </h4>
                                        <p><strong>申請人:</strong> {{ $request->user->name }} ({{ $request->user->email }})</p>
                                        <p><strong>運送方式:</strong> {{ $request->delivery_method_text }}</p>
                                        <p><strong>原本運費:</strong> NT${{ number_format($request->original_shipping_fee) }}</p>
                                        @if($request->new_shipping_fee)
                                            <p><strong>新運費:</strong> NT${{ number_format($request->new_shipping_fee) }}</p>
                                        @endif
                                        <p><strong>狀態:</strong>
                                            @if($request->status == 0)
                                                <span class="uk-label uk-label-warning">待處理</span>
                                            @elseif($request->status == 1)
                                                <span class="uk-label uk-label-success">已處理</span>
                                            @elseif($request->status == 3)
                                                <span class="uk-label uk-label-primary">已完成</span>
                                            @else
                                                <span class="uk-label uk-label-danger">已拒絕</span>
                                            @endif
                                        </p>
                                        <p class="uk-text-small">申請時間: {{ $request->created_at->format('Y-m-d H:i') }}</p>
                                    </div>
                                    <div class="uk-width-auto">
                                        <a href="{{ route('auctioneer.merge_shipping_requests.show', $request->id) }}" class="uk-button custom-button-1">
                                            查看詳情
                                        </a>
                                    </div>
                                </div>

                                <div class="uk-margin">
                                    <h5>包含商品 ({{ $request->items->count() }} 件):</h5>
                                    <div class="uk-grid-small uk-child-width-1-4" uk-grid>
                                        @foreach($request->items->take(4) as $item)
                                            <div>
                                                <div class="uk-card uk-card-default uk-card-small">
                                                    <div class="uk-card-media-top">
                                                        <img src="{{ $item->lot->blImages->first()->url }}" alt="" style="height: 80px; object-fit: cover;">
                                                    </div>
                                                    <div class="uk-card-body">
                                                        <h6 class="uk-card-title uk-text-small">{{ $item->lot->name }}</h6>
                                                        <p class="uk-text-small">數量: {{ $item->quantity }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($request->items->count() > 4)
                                            <div class="uk-width-1-4">
                                                <div class="uk-card uk-card-default uk-card-small">
                                                    <div class="uk-card-body uk-text-center">
                                                        <p class="uk-text-small">還有 {{ $request->items->count() - 4 }} 件商品...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <p class="uk-text-center">目前沒有合併運費請求</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
