@extends('layouts.member')

@section('content')
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-3@s">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">註冊</h1>
            </div>
            <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
                <ul id="validator-alert-ul"></ul>
            </div>
            <form class="uk-form-stacked" id="register-form">
                <div class="uk-margin">
                    <label class="uk-form-label" for="name">真實姓名</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-input" id="name" name="name" required>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="email">電子郵件</label>
                    <div class="uk-form-controls">
                        <input type="email" class="uk-input" id="email" name="email" required>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="phone">電話號碼</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="tel" id="phone" name="phone"
                               pattern="[0-9]{10}" required>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password">密碼</label>
                    <div class="uk-form-controls">
                        <input type="password" class="uk-input" id="password" name="password" required
                               autocomplete="new-password">
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password_confirmation">再確認一次密碼</label>
                    <div class="uk-form-controls">
                        <input type="password" class="uk-input" id="password_confirmation" name="password_confirmation"
                               required autocomplete="new-password">
                    </div>
                </div>

                <div class="uk-margin">
                    <button class="g-recaptcha uk-button custom-button-1 uk-width-1-1"
                            data-sitekey="6Lce1CAqAAAAAGoDOkmLMxcKOBQEkYb_EQbwQqwg"
                            data-callback='onSubmit'
                            data-action='submit'>註冊</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        function onSubmit(token) {
            $('#register-form').submit();
        }

        $(function () {
            $('#register-form').submit(function (e) {
                e.preventDefault();
                let inputData = new FormData(this);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('register') }}',
                    data: inputData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: '註冊成功',
                            showConfirmButton: false,
                        })

                        function successAction(response) {
                            window.location.replace(response.success);
                        }

                        setTimeout(function () {
                            successAction(response);
                        }, 1500);
                    },
                    error: function (response) {
                        Swal.close();
                        let errors = merge_errors(response);
                        let validatorAlert = $('#validator-alert');
                        validatorAlert.prop('hidden', false);
                        let validatorAlertUl = $('#validator-alert-ul');
                        validatorAlertUl.empty();
                        validatorAlertUl.append(errors);
                        $('html,body').animate({scrollTop: 0}, 500);
                    }
                });
            });
        });
    </script>
@endpush
