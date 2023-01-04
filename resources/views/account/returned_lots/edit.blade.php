@extends('layouts.member')
@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
        <ul id="validator-alert-ul"></ul>
    </div>
    <form id="returned-lot-handle-form">
        <div class="uk-margin">
            <div class="uk-card uk-card-default uk-card-body">
                <div class="uk-margin">
                    <label>收件人姓名
                        <input class="uk-input" type="text" name="addressee_name">
                    </label>
                </div>
                <div class="uk-margin">
                    <label>收件人電話
                        <input class="uk-input" type="tel" name="addressee_phone"
                               pattern="[0-9]{3}[0-9]{3}[0-9]{4}">
                    </label>
                </div>
                <div class="sub-field uk-margin uk-accordion-content" id="home-delivery-field">
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
            </div>
        </div>
        <div class="uk-margin uk-flex uk-flex-right">
            <a href="#confirm-submit-delivery-info" rel="modal:open" class="uk-button custom-button-1 uk-width-auto@s">送出</a>
        </div>
        <div id="confirm-submit-delivery-info" class="modal">
            <h2>確定送出處理方式嗎？</h2>
            <p class="uk-text-right">
                <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                <a class="uk-button custom-button-1 submitDeliveryInfo" lotId="{{ $lotId }}">確定</a>
            </p>
        </div>
    </form>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('js/jQuery-TWzipcode/twzipcode.js') }}"></script>
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
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

            $('.submitDeliveryInfo').on('click', function () {

                let lotId = $(this).attr('lotId');
                let data = $('#returned-lot-handle-form').serialize();

                let url = '{{ route("account.returned_lots.update", [":lotId"]) }}';
                url = url.replace(':lotId', lotId);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: url,
                    data: data,
                    success: function (response) {
                        $.modal.close();
                        if (typeof (response.error) !== 'undefined') {
                            let errors = Object.values(response.error)
                            $('#validator-alert').prop('hidden', false);
                            let validatorAlertUl = $('#validator-alert-ul');
                            validatorAlertUl.empty();
                            errors.forEach(i => validatorAlertUl.append($("<li></li>").text(i)));
                            $('html,body').animate({scrollTop: 0}, 500);
                        } else {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: '已送出',
                                showConfirmButton: false,
                            })

                            setTimeout(function() {
                                window.location.assign('{{ route('account.returned_lots.index') }}');
                            }, 1000);
                        }
                    }
                });
            });
        });
    </script>
@endpush
