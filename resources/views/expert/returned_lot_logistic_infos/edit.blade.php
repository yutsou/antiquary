@extends('layouts.expert')
@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
        <ul id="validator-alert-ul"></ul>
    </div>
    @if($lot->status != 32)
        <div class="uk-margin">
            <div class="uk-card uk-card-default uk-card-body">
                <ul class="uk-list">

                    <li>{{ $logisticInfo->addressee_name ?? '' }}</li>
                    <li>{{ $logisticInfo->addressee_phone ?? '' }}</li>
                    <li>郵遞區號：{{ $logisticInfo->delivery_zip_code ?? '' }}</li>
                    <li>縣市：{{ $logisticInfo->county ?? '' }}</li>
                    <li>區：{{ $logisticInfo->district ?? '' }}</li>
                    <li>街道地址：{{ $logisticInfo->delivery_address ?? '' }}</li>

                </ul>
            </div>
        </div>
    @endif
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body">
            <div class="uk-margin">
                <label class="uk-form-label" for="logistic-name">物流公司名稱</label>
                <div class="uk-form-controls">
                    <input type="text"  class="uk-input" id="logistic-name" placeholder="例如：黑貓">
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-form-label" for="tracking-code">物流追蹤碼</label>
                <div class="uk-form-controls">
                    <input type="text"  class="uk-input" id="tracking-code">
                </div>
            </div>
        </div>
    </div>
    <div class="uk-margin uk-text-right">
        <a href="#confirm-submit-delivery-info" rel="modal:open" class="uk-button custom-button-1">送出</a>
    </div>
    <div id="confirm-submit-delivery-info" class="modal">
        <h2>確定送出物流資訊嗎？</h2>
        <p class="uk-text-right">
            <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
            <a class="uk-button custom-button-1 submitDeliveryInfo" lotId="{{ $lot->id }}" mainCategoryId="{{ $mainCategoryId }}">確定</a>
        </p>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
        $(function () {
            $('.submitDeliveryInfo').on('click', function () {

                let lotId = $(this).attr('lotId');
                let mainCategoryId = $(this).attr('mainCategoryId');
                let logisticName = $('#logistic-name').val();
                let trackingCode = $('#tracking-code').val();

                let url = '{{ route("expert.returned_lot_logistic_info.update", [":mainCategoryId", ":lotId"]) }}';
                url = url.replace(':mainCategoryId', mainCategoryId);
                url = url.replace(':lotId', lotId);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: url,
                    data: { company_name:logisticName, tracking_code:trackingCode },
                    success: function (data) {
                        $.modal.close();
                        if (typeof (data.error) !== 'undefined') {
                            let errors = Object.values(data.error)
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
                                window.location.assign('{{ route('expert.lots.index', $mainCategoryId) }}');
                            }, 1000);
                        }
                    }
                });
            });
        });
    </script>
@endpush
