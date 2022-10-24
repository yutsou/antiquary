@extends('layouts.member')
@inject('methodPresenter', 'App\Presenters\MethodPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.orders.index') }}" class="custom-color-1 custom-link-mute">已得標的物品</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            <div class="uk-margin">
                <div class="uk-child-width-expand uk-grid-collapse" uk-grid>
                    <div class="uk-first-column">
                        <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                            選擇付款方式
                        </div>
                    </div>
                    <div>
                        <div class="uk-text-center" style="border-bottom: 2px solid #003a6c;">
                            選擇運送方式
                        </div>
                    </div>
                    <div>
                        <div class="uk-text-center" style="border-bottom: 1px solid #cccccc;">
                            訂單確認
                        </div>
                    </div>
                </div>
            </div>
            <form class="uk-form-stacked" method="POST" action="{{ route('account.orders.update', $order) }}"
                  enctype="multipart/form-data">
                @csrf
                <input class="uk-radio" type="text" name="paymentMethod" value="{{ $paymentMethod }}" hidden>
                <input type="number" id="delivery-cost" name="deliveryCost" hidden>
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">您得標的物品提供以下的運送方式</h3>
                        <ul class="uk-list">
                            @foreach($order->lot->deliveryMethods as $method)
                                <li>
                                    <label><input class="uk-radio paymentMethod" type="radio" name="deliveryMethod"
                                                  value="{{ $method->code }}"
                                                  cost="{{ $method->cost }}"> {{ $methodPresenter->transferDeliveryMethod($method->code) }}
                                        - NT${{ number_format($method->cost) }}</label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="uk-margin" id="delivery-field" hidden>
                    <div class="uk-card uk-card-default uk-card-body">
                        <div class="uk-margin">
                            <div class="uk-margin">
                                <label>收貨人/取貨人 姓名
                                    <input class="uk-input" type="text" name="recipient_name">
                                </label>
                            </div>
                            <div class="uk-margin">
                                <label>收貨人/取貨人 電話
                                    <input class="uk-input" type="tel" name="recipient_phone"
                                           pattern="[0-9]{3}[0-9]{3}[0-9]{4}">
                                </label>
                            </div>

                        </div>
                        <div class="sub-field uk-margin" id="face-to-face-field" hidden>
                            @if($order->lot->entrust === 0)
                                <p>這件物品的取貨地點需要與賣家協調</p>
                            @else
                                <p>物品的取貨地址為：台北市信義區信義路五段7號</p>
                            @endif

                        </div>
                        <div class="sub-field uk-margin" id="home-delivery-field" hidden>
                            <h3 class="uk-card-title">收件人縣市、鄉鎮</h3>
                            <div class="uk-grid-small uk-child-width-1-3 uk-form-controls twzipcode" uk-grid>
                                <div data-role="county" data-style="uk-select" data-name="county"
                                     data-value="{{ Auth::user()->county ?? null }}"></div>
                                <div data-role="district" data-style="uk-select" data-name="district"
                                     data-value="{{ Auth::user()->district ?? null }}"></div>
                                <div data-role="zipcode" data-style="uk-select" data-name="zipcode"
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
                    <button class="uk-button custom-button-1">下一步</button>
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
            // set by County, District
            twzipcode.set({
                'county': '',
                'district': ''
            });
        }

        $(function () {
            const twzipcode = new TWzipcode(".twzipcode",
                {
                    'county': {
                        'css': 'uk-select',
                        'required': false,     // 是否為表單必須
                        'onSelect': function (e) { // change 事件
                            // HTMLSelectElement
                            //$('#address').val('');
                        }
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
                }
            );

            $("#country_selector").countrySelect({
                // defaultCountry: "jp",
                // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
                // responsiveDropdown: true,
                // preferredCountries: ['ca', 'gb', 'us']
            });

            $('.paymentMethod[value=0]').click(function () {
                hideAllSubField();
                $('#face-to-face-field').prop('hidden', false);
                $('#delivery-cost').val($(this).attr('cost'));//set cost value
            });
            $('.paymentMethod[value=1]').click(function () {
                hideAllSubField();
                $('#home-delivery-field').prop('hidden', false);
                $('#delivery-cost').val($(this).attr('cost'));
            });
            $('.paymentMethod[value=2]').click(function () {
                hideAllSubField();
                $('#cross-border-delivery-field').prop('hidden', false);
                $('#delivery-cost').val($(this).attr('cost'));
            });

        });
    </script>
@endpush
