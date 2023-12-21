@extends('layouts.member')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.applications.index') }}" class="custom-color-1 custom-link-mute">審核中的申請</a></a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-small">{{ $head }}</h1>
            </div>
            <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
                <ul id="validator-alert-ul"></ul>
            </div>
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <ul class="uk-list">
                        <li>愛帝奎電商有限公司</li>
                        <li>02-27186473</li>
                        <li>105台北市松山區敦化北路222巷2號1樓</li>
                    </ul>
                </div>
            </div>
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
            <div class="uk-margin uk-flex uk-flex-right">
                <a href="#confirm-submit-delivery-info" rel="modal:open" class="uk-button custom-button-1 uk-width-auto@s">送出</a>
            </div>
            <div id="confirm-submit-delivery-info" class="modal">
                <h2>確定送出物流資訊嗎？</h2>
                <p class="uk-text-right">
                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                    <a class="uk-button custom-button-1 submitDeliveryInfo " lotId="{{ $lotId }}">確定</a>
                </p>
            </div>
        </div>
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
                let logisticName = $('#logistic-name').val();
                let trackingCode = $('#tracking-code').val();

                let url = '{{ route("account.application_logistic_info.store", [":lotId"]) }}';
                url = url.replace(':lotId', lotId);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: url,
                    data: { logistic_name:logisticName, tracking_code:trackingCode },
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
                                window.location.assign('{{ route('account.applications.index') }}');
                            }, 1000);
                        }
                    }
                });
            });
        });
    </script>
@endpush
