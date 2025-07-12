@extends('layouts.auctioneer')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('auctioneer.dashboard') }}" class="custom-color-1 custom-link-mute">管理員中心</a> > <a href="{{ route('auctioneer.merge_shipping_requests.index') }}" class="custom-color-1 custom-link-mute">合併運費請求管理</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>

    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>

            <div class="uk-grid" uk-grid>
                <div class="uk-width-2-3">
                    <!-- 請求詳情 -->
                    <div class="uk-card uk-card-default uk-margin">
                        <div class="uk-card-header">
                            <h3 class="uk-card-title">請求詳情</h3>
                        </div>
                        <div class="uk-card-body">
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-1-2">
                                    <p><strong>請求編號:</strong> #{{ $request->id }}</p>
                                    <p><strong>申請人:</strong> {{ $request->user->name }}</p>
                                    <p><strong>電子郵件:</strong> {{ $request->user->email }}</p>
                                    <p><strong>電話:</strong> {{ $request->user->phone ?? '未提供' }}</p>
                                </div>
                                <div class="uk-width-1-2">
                                    <p><strong>運送方式:</strong> {{ $request->delivery_method_text }}</p>
                                    <p><strong>原本運費:</strong> NT${{ number_format($request->original_shipping_fee) }}</p>
                                    <p><strong>申請時間:</strong> {{ $request->created_at->format('Y-m-d H:i') }}</p>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 包含商品 -->
                    <div class="uk-card uk-card-default uk-margin">
                        <div class="uk-card-header">
                            <h3 class="uk-card-title">包含商品 ({{ $request->items->count() }} 件)</h3>
                        </div>
                        <div class="uk-card-body">
                            <div class="uk-grid-small uk-child-width-1-3" uk-grid>
                                @foreach($request->items as $item)
                                    <div>
                                        <div class="uk-card uk-card-default uk-card-small">
                                            <div class="uk-card-media-top">
                                                <img src="{{ $item->lot->blImages->first()->url }}" alt="" style="height: 120px; object-fit: cover;">
                                            </div>
                                            <div class="uk-card-body">
                                                <h6 class="uk-card-title">{{ $item->lot->name }}</h6>
                                                <p class="uk-text-small">數量: {{ $item->quantity }}</p>
                                                <p class="uk-text-small">原本運費: NT${{ number_format($item->original_shipping_fee) }}</p>
                                                <p class="uk-text-small">商品價格: NT${{ number_format($item->lot->reserve_price) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uk-width-1-3">
                    <!-- 處理表單 -->
                    @if($request->status == 0)
                        <div class="uk-card uk-card-default uk-margin">
                            <div class="uk-card-header">
                                <h3 class="uk-card-title">處理請求</h3>
                            </div>
                            <div class="uk-card-body">
                                <form id="process-form" method="POST" action="{{ route('auctioneer.merge_shipping_requests.update', $request->id) }}">
                                    @csrf
                                    <div class="uk-margin">
                                        <label class="uk-form-label" for="new_shipping_fee">新運費 (NT$)</label>
                                        <div class="uk-form-controls">
                                            <input class="uk-input" type="number" id="new_shipping_fee" name="new_shipping_fee" min="0" step="1" required>
                                            <small class="uk-text-muted">拒絕合併運費時可不填寫</small>
                                        </div>
                                    </div>

                                    <div class="uk-margin">
                                        <label class="uk-form-label" for="status">處理結果</label>
                                        <div class="uk-form-controls">
                                            <select class="uk-select" id="status" name="status" required>
                                                <option value="">請選擇</option>
                                                <option value="1">同意合併運費</option>
                                                <option value="2">拒絕合併運費</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="uk-margin">
                                        <label class="uk-form-label" for="remark">備註</label>
                                        <div class="uk-form-controls">
                                            <textarea class="uk-textarea" id="remark" name="remark" rows="3" placeholder="可選填備註說明"></textarea>
                                        </div>
                                    </div>

                                    <div class="uk-margin">
                                        <button type="submit" class="uk-button custom-button-1 uk-width-1-1">提交處理結果</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="uk-card uk-card-default uk-margin">
                            <div class="uk-card-header">
                                <h3 class="uk-card-title">處理結果</h3>
                            </div>
                            <div class="uk-card-body">
                                @if($request->status == 1 || $request->status == 3)
                                    <p><strong>新運費:</strong> NT${{ number_format($request->new_shipping_fee) }}</p>
                                @endif
                                <p><strong>處理結果:</strong>
                                    @if($request->status == 1)
                                        <span class="uk-label uk-label-success">同意合併運費</span>
                                    @elseif($request->status == 2)
                                        <span class="uk-label uk-label-danger">拒絕合併運費</span>
                                    @elseif($request->status == 3)
                                        <span class="uk-label uk-label-primary">已完成</span>
                                    @endif
                                </p>
                                @if($request->remark)
                                    <p><strong>備註:</strong> {{ $request->remark }}</p>
                                @endif
                                <p><strong>處理時間:</strong> {{ $request->updated_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            // 處理狀態選擇變更
            $('#status').on('change', function() {
                var status = $(this).val();
                var newShippingFeeInput = $('#new_shipping_fee');

                if (status == '2') {
                    // 拒絕合併運費時，移除必填屬性
                    newShippingFeeInput.removeAttr('required');
                    newShippingFeeInput.addClass('uk-form-blank');
                } else if (status == '1') {
                    // 同意合併運費時，添加必填屬性
                    newShippingFeeInput.attr('required', 'required');
                    newShippingFeeInput.removeClass('uk-form-blank');
                }
            });

            $('#process-form').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '處理成功',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000,
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = '處理失敗！';
                        if (xhr.status === 400 && xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).map(function(arr){
                                return arr.join('<br>');
                            }).join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            html: errorMsg,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    }
                });
            });
        });
    </script>
@endpush
