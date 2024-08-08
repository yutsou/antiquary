@extends('layouts.member')

@section('content')
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-3@s">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
                <ul id="validator-alert-ul"></ul>
            </div>
            <form class="uk-form-stacked" id="login-form">
                @csrf
                <input type="hidden" name="redirectUrl" value="{{ $redirectUrl ?? '' }}">
                <input type="hidden" name="linkToken" value="{{ request()->get('linkToken') ?? '' }}">

                <div class="uk-margin">
                    <label class="uk-form-label" for="email">電子郵件</label>
                    <div class="uk-form-controls">
                        <input type="email" class="uk-input" id="email" name="email" required>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password">密碼</label>
                    <div class="uk-form-controls">
                        <input type="password" class="uk-input" id="password" name="password" required
                               autocomplete="password">
                    </div>
                </div>

                <div class="uk-margin">
                    <a class="uk-link-text " href="{{ route('auth.password_forgot.show') }}">忘記密碼？</a>
                </div>
                <div class="uk-margin">
                    <button class="g-recaptcha uk-button custom-button-1 uk-width-1-1"
                            data-sitekey="6Lce1CAqAAAAAGoDOkmLMxcKOBQEkYb_EQbwQqwg"
                            data-callback='onSubmit'
                            data-action='submit'>登入</button>
                </div>
            </form>
            <div class="separator">或是</div>
            <div class="uk-margin">
                <a class="uk-button uk-text-capitalize uk-width-1-1" href="{{ route('auth.google.handle') }}" style="background-color: #fff; color: #666; border: solid #e5e5e5 1px;">
                    <img width="20px" style="margin-bottom:3px; margin-right:5px;" src="{{ asset('/images/web/common/google_login.png') }}" />
                    使用 Google 登入
                </a>
            </div>
            <div class="uk-margin">
                <a href="{{ route('auth.line.login') }}" class="uk-button uk-text-capitalize uk-width-1-1" href="" style="background-color: #00b900; color: #fff; border: solid #e5e5e5 1px;">
                    <img width="20px" style="margin-bottom:3px; margin-right:5px;" src="{{ asset('images/web/common/line_login_reverse.png') }}" />
                    使用 LINE 登入
                </a>
            </div>
            <div class="uk-margin uk-text-center">
                <a class="uk-link-text" href="{{ route('register') }}">註冊新會員</a>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        function onSubmit(token) {
            $('#login-form').submit();
        }

        $(function () {
            $('#login-form').submit(function (e) {
                e.preventDefault();
                let inputData = new FormData(this);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('login') }}',
                    data: inputData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: '登入成功',
                            showConfirmButton: false,
                        })

                        function successAction(response) {
                            window.location.replace(response.success);
                        }

                        setTimeout(function () {
                            successAction(response);
                        }, 1000);
                    },
                    error: function (response) {
                        Swal.close();
                        let errors = mergeErrors(response);
                        let validatorAlert = $('#validator-alert');
                        validatorAlert.prop('hidden', false);
                        let validatorAlertUl = $('#validator-alert-ul');
                        validatorAlertUl.empty();
                        validatorAlertUl.append(errors);
                        $('html,body').animate({scrollTop: 0}, 500);
                    }
                });
            });

            function mergeErrors(response) {
                let errors = response.responseJSON.errors;
                let errorList = '';
                $.each(errors, function (key, value) {
                    errorList += '<li>' + value[0] + '</li>';
                });
                return errorList;
            }
        });
    </script>
@endpush
