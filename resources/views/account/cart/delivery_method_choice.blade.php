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

            <form class="uk-form-stacked" method="POST" action="{{ route('account.cart.payment_method_choice') }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="delivery-cost" name="delivery_cost">
                @foreach($selectedLotIds as $lotId)
                    <input type="hidden" name="selected_lots[]" value="{{ $lotId }}">
                @endforeach
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
                        <h3 class="uk-card-title">選中的商品</h3>
                        <div class="uk-margin">
                        @foreach($selectedLots as $lot)
                                <div class="uk-card uk-card-default uk-grid-collapse uk-margin" uk-grid>
                                    <div class="uk-card-media-left uk-cover-container uk-width-1-5">
                                        <img src="{{ $lot->blImages->first()->url }}" alt="" uk-cover>
                                    </div>
                                    <div class="uk-width-expand">
                                        <div class="uk-card-body" style="padding: 20px 20px">
                                            <h3 class="uk-card-title" style="margin: 0 0 10px 0">{{ $lot->name }}</h3>
                                            <p>數量: {{ $lot->cart_quantity }} | 小計: NT${{ number_format($lot->subtotal) }}</p>
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

                            @if(!empty($commonDeliveryCodes))
                                @foreach($selectedLots->first()->deliveryMethods->whereIn('code', $commonDeliveryCodes) as $method)
                                    <li>
                                        <label>
                                            <input class="uk-radio deliveryMethod" type="radio" name="delivery_method" value="{{ $method->code }}" cost="{{ $method->code == 1 ? $homeDeliveryTotal : ($method->code == 2 ? $crossBorderTotal : $method->cost) }}">
                                            {{ $methodPresenter->transferDeliveryMethod($method->code) }} - NT${{ number_format($method->code == 1 ? $homeDeliveryTotal : ($method->code == 2 ? $crossBorderTotal : $method->cost)) }}
                                        </label>
                                    </li>
                                    @if($method->code == 1 && $totalQuantity > 1)
                                        <li>
                                            <label>
                                                <input class="uk-radio deliveryMethod" type="radio" name="delivery_method" value="1-merge" cost="0">
                                                宅配合併運送（請洽詢拍賣師）
                                            </label>
                                        </li>
                                    @endif
                                    @if($method->code == 2 && $totalQuantity > 1)
                                        <li>
                                            <label>
                                                <input class="uk-radio deliveryMethod" type="radio" name="delivery_method" value="2-merge" cost="0">
                                                境外物流合併運送（請洽詢拍賣師）
                                            </label>
                                        </li>
                                    @endif
                        @endforeach
                            @else
                                <li>沒有共同的運送方式可選擇</li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="uk-margin" id="delivery-field" hidden>
                    <div class="uk-card uk-card-default uk-card-body">
                        <div class="uk-margin">
                            <div class="uk-margin">
                                <label>收貨人/取貨人 姓名
                                    <input class="uk-input" type="text" name="recipient_name" id="recipient-name">
                                </label>
                            </div>
                            <div class="uk-margin">
                                <label>收貨人/取貨人 電話
                                    <input class="uk-input" type="tel" name="recipient_phone" id="recipient-phone" pattern="[0-9]{3}[0-9]{3}[0-9]{4}">
                                </label>
                            </div>
                        </div>
                        <div class="sub-field uk-margin" id="face-to-face-field" hidden>
                            <p>取貨資訊：</p>
                            <ul>
                                <li>地址: 105台北市松山區敦化北路222巷2號1樓 (19世紀歐洲古董&復古收藏)</li>
                                <li>電話: 02-27186473</li>
                            </ul>
                        </div>
                        <div class="sub-field uk-margin" id="home-delivery-field" hidden>
                            <h3 class="uk-card-title">收件人縣市、鄉鎮</h3>
                            <div class="uk-grid-small uk-child-width-1-3 uk-form-controls twzipcode" uk-grid>
                                <div data-role="county" data-style="uk-select" data-name="county" id="county"
                                     data-value="{{ Auth::user()->county ?? null }}"></div>
                                <div data-role="district" data-style="uk-select" data-name="district" id="district"
                                     data-value="{{ Auth::user()->district ?? null }}"></div>
                                <div data-role="zipcode" data-style="uk-select" data-name="zip_code" id="zip-code"
                                     data-value="{{ Auth::user()->zip_code ?? null }}"></div>
                            </div>
                            <div class="uk-margin">
                                <label class="uk-form-label" for="address">街道地址</label>
                                <div class="uk-form-controls">
                                    <input class="uk-input" type="text" id="address" name="address" placeholder="輸入您的地址"
                                           value="{{ Auth::user()->address ?? null }}" autocomplete="street-address">
                                </div>
                            </div>
                        </div>
                        <div class="sub-field uk-margin" id="cross-border-delivery-field" hidden>
                            <h3 class="uk-card-title">選擇國家</h3>
                            <div class="form-item">
                                <input class="uk-input uk-width-1-3" id="country_selector" name="country" type="text" readonly>
                                <label for="country_selector" style="display:none;">Select a country here...</label>
                            </div>
                            <div class="form-item" style="display:none;">
                                <input type="text" id="country_selector_code" name="country_selector_code"
                                       data-countrycodeinput="1" readonly placeholder="Selected country code will appear here"/>
                                <label for="country_selector_code">...and the selected country code will be updated here</label>
                            </div>
                            <button type="submit" style="display:none;">Submit</button>
                            <div class="uk-margin">
                                <label class="uk-form-label" for="cross-board-address">跨境目的地完整地址</label>
                                <div class="uk-form-controls">
                                    <input class="uk-input" type="text" id="cross-board-address" name="cross_board_address"
                                           value="{{ Auth::user()->address ?? null }}">
                                </div>
                            </div>
                        </div>
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
            $('.deliveryMethod').click(function () {
                var val = $(this).val();
                var cost = $(this).attr('cost');

                // 設置 delivery_method 的值
                $('#deliveryMethod').val(val);

                // 如果是合併運費，修改表單 action 到合併運費請求
                if (val === '1-merge' || val === '2-merge') {
                    $('form').attr('action', '{{ route("account.cart.merge_shipping_request") }}');
                } else {
                    // 重置表單 action 為正常的付款方式選擇
                    $('form').attr('action', '{{ route("account.cart.payment_method_choice") }}');
                }

                // 顯示運送資訊欄位（合併運費不需要填寫運送資訊）
                if (val === '1-merge' || val === '2-merge') {
                    // 合併運費不需要填寫運送資訊，隱藏所有運送欄位
                    $('#delivery-field').prop('hidden', true);
                } else {
                    // 一般運送方式需要填寫運送資訊
                    hideAllSubField();
                    if(val == '0') {
                        $('#face-to-face-field').prop('hidden', false);
                    } else if(val == '1') {
                        $('#home-delivery-field').prop('hidden', false);
                    } else if(val == '2') {
                        $('#cross-border-delivery-field').prop('hidden', false);
                    }
                }

                $('#delivery-cost').val(cost);
            });

        });
    </script>
@endpush
