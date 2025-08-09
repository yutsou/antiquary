@extends('layouts.member')
@inject('methodPresenter', 'App\\Presenters\\MethodPresenter')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.cart.show') }}" class="custom-color-1 custom-link-mute">購物車</a>
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

            <form class="uk-form-stacked" method="POST" action="{{ route('account.cart.merge_shipping_delivery.update', $mergeRequest->id) }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="delivery_method" id="delivery-method" value="{{ $mergeRequest->delivery_method }}">
                <input type="hidden" name="delivery_cost" id="delivery-cost" value="{{ $mergeRequest->new_shipping_fee }}">

                <div class="uk-margin">
                    <div class="uk-child-width-expand uk-grid-collapse" uk-grid>
                        <div class="uk-first-column">
                            <div class="uk-text-center" style="border-bottom: 2px solid #003a6c;">
                                選擇運送方式
                            </div>
                        </div>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                選擇付款方式
                            </div>
                        </div>
                        <div>
                            <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                                訂單確認
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">合併運費請求 #{{ $mergeRequest->id }}</h3>
                        <p><strong>原本運費:</strong> NT${{ number_format($mergeRequest->original_shipping_fee) }}</p>
                        <p><strong>新運費:</strong> NT${{ number_format($mergeRequest->new_shipping_fee) }}</p>
                        <p><strong>運送方式:</strong> {{ $mergeRequest->delivery_method_text }}</p>
                        @if($mergeRequest->remark)
                            <p><strong>備註:</strong> {{ $mergeRequest->remark }}</p>
                        @endif
                    </div>
                </div>

                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">包含商品</h3>
                        <div class="uk-margin">
                            @foreach($mergeRequest->items as $item)
                                <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                                    <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                                        <img src="{{ $item->lot->blImages->first()->url }}" alt="" uk-cover>
                                    </div>
                                    <div class="uk-width-expand">
                                        <div class="uk-card-body" style="padding: 20px 20px">
                                            <h3 class="uk-card-title" style="margin: 0 0 10px 0">{{ $item->lot->name }}</h3>
                                            <p>數量: {{ $item->quantity }} | 小計: NT${{ number_format($item->lot->reserve_price * $item->quantity) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">請選擇運送方式</h3>
                        <ul class="uk-list">
                            <li>
                                <label>
                                    <input class="uk-radio deliveryMethod" type="radio" name="deliveryMethodRadio" value="{{ $mergeRequest->delivery_method }}" cost="{{ $mergeRequest->new_shipping_fee }}" checked>
                                    {{ $mergeRequest->delivery_method_text }} - NT${{ number_format($mergeRequest->new_shipping_fee) }}
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>

                @php
                    $logisticRecord = $mergeRequest->logisticRecords->where('type', 0)->first();
                @endphp
                <div class="uk-margin" id="delivery-field">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">收貨地址</h3>
                        <div class="uk-margin">
                            <div class="uk-margin">
                                <label>收貨人/取貨人 姓名
                                    <input class="uk-input" type="text" name="recipient_name" value="{{ $logisticRecord->addressee_name ?? '' }}" readonly>
                                </label>
                            </div>
                            <div class="uk-margin">
                                <label>收貨人/取貨人 電話
                                    <input class="uk-input" type="tel" name="recipient_phone" value="{{ $logisticRecord->addressee_phone ?? '' }}" readonly>
                                </label>
                            </div>
                        </div>
                        @if($mergeRequest->delivery_method == 1)
                            <div class="sub-field uk-margin" id="home-delivery-field">
                                <h3 class="uk-card-title">收件人縣市、鄉鎮</h3>
                                <div class="uk-grid-small uk-child-width-1-3 uk-form-controls twzipcode" uk-grid>
                                    <div data-role="county" data-style="uk-select" data-name="county" id="county"
                                         data-value="{{ $logisticRecord->county ?? '' }}"></div>
                                    <div data-role="district" data-style="uk-select" data-name="district" id="district"
                                         data-value="{{ $logisticRecord->district ?? '' }}"></div>
                                    <div data-role="zipcode" data-style="uk-select" data-name="zip_code" id="zip-code"
                                         data-value="{{ $logisticRecord->delivery_zip_code ?? '' }}"></div>
                                </div>
                                <div class="uk-margin">
                                    <label class="uk-form-label" for="address">街道地址</label>
                                    <div class="uk-form-controls">
                                        <input class="uk-input" type="text" id="address" name="address" value="{{ $logisticRecord->delivery_address ?? '' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        @elseif($mergeRequest->delivery_method == 2)
                            <div class="sub-field uk-margin" id="cross-border-delivery-field">
                                <h3 class="uk-card-title">選擇國家</h3>
                                <div class="form-item">
                                    <input class="uk-input uk-width-1-3" id="country_selector" name="country" type="text" value="{{ $logisticRecord->cross_board_delivery_country ?? '' }}" readonly>
                                    <label for="country_selector" style="display:none;">Select a country here...</label>
                                </div>
                                <div class="form-item" style="display:none;">
                                    <input type="text" id="country_selector_code" name="country_selector_code"
                                           data-countrycodeinput="1" value="{{ $logisticRecord->cross_board_delivery_country_code ?? '' }}" readonly placeholder="Selected country code will appear here"/>
                                    <label for="country_selector_code">...and the selected country code will be updated here</label>
                                </div>
                                <button type="submit" style="display:none;">Submit</button>
                                <div class="uk-margin">
                                    <label class="uk-form-label" for="cross-board-address">跨境目的地完整地址</label>
                                    <div class="uk-form-controls">
                                        <input class="uk-input" type="text" id="cross-board-address" name="cross_board_address"
                                               value="{{ $logisticRecord->cross_board_delivery_address ?? '' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="uk-margin uk-align-right">
                    <button class="uk-button custom-button-1" type="submit">下一步</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/countrySelect/css/countrySelect.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/jQuery-TWzipcode/twzipcode.js') }}"></script>
    <script src="{{ asset('extensions/countrySelect/js/countrySelect.js') }}"></script>
    <script>
        let hideAllSubField = function () {
            $('#delivery-field').prop('hidden', false);
            $('.sub-field').prop('hidden', true);
        }
        let clearAllSubField = function (twzipcode) {
            twzipcode.set({
                'county': '',
                'district': ''
            });
        }
        $(function () {
            const twzipcode = new TWzipcode(".twzipcode", {
                'county': {
                    'css': 'uk-select',
                    'required': false,
                    'onSelect': function (e) {}
                },
                'district': {
                    'required': false,
                    'css': 'uk-select',
                },
                'zipcode': {
                    'required': false,
                    'css': 'uk-input',
                    'readonly': true,
                },
            });
            $("#country_selector").countrySelect({});

            // 自動選擇運送方式並顯示對應欄位
            var delivery_method = {{ $mergeRequest->delivery_method }};
            $('input[value="' + delivery_method + '"]').prop('checked', true);

            // 地址信息已經顯示，不需要隱藏
            $('#delivery-method').val(delivery_method);

            // 讓所有選擇欄位變成只讀
            setTimeout(function() {
                // 讓縣市選擇器的下拉選單變成只讀
                $('.twzipcode select').prop('readonly', true);
                $('.twzipcode select').addClass('uk-form-blank');

                // 讓國家選擇器變成只讀
                $('#country_selector').prop('readonly', true);
                $('#country_selector').addClass('uk-form-blank');

                // 防止用戶點擊選擇器
                $('.twzipcode select').on('click', function(e) {
                    e.preventDefault();
                    return false;
                });

                $('#country_selector').on('click', function(e) {
                    e.preventDefault();
                    return false;
                });
            }, 1000);

            $('.deliveryMethod').click(function () {
                var val = $(this).val();
                var cost = $(this).attr('cost');

                $('#delivery-method').val(val);
            });
        });
    </script>
@endpush
