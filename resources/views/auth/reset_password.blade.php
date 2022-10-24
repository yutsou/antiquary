@extends('layouts.member')

@section('content')
    <div class="uk-width-1-2 uk-align-center">
        <div class="uk-margin-medium">
            <h1 class="uk-heading-medium">帳戶設定</h1>
        </div>
        <form class="uk-form-stacked" id="profile-form">
            <input class="uk-input" type="text" name="token" value="{{ $token }}" hidden>
            <div class="uk-margin">
                <label class="uk-form-label" for="password">新的密碼</label>
                <div class="uk-form-controls">
                    <input class="uk-input" id="password" type="password" name="password" autocomplete="new-password" required>
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-form-label" for="passwordConfirmation">重複新的密碼</label>
                <div class="uk-form-controls">
                    <input class="uk-input" id="passwordConfirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>
            </div>

            <div class="uk-margin">
                <div class="uk-alert-warning alert" id="validator-alert" uk-alert hidden>
                    <ul id="validator-alert-ul"></ul>
                </div>
                <div class="uk-alert-success alert" uk-alert id="update-success" hidden>
                    <a class="uk-alert-close" uk-close></a>
                    <p>修改成功</p>
                </div>
            </div>
            <div class="uk-margin uk-text-right">
                <button class="uk-button custom-button-1" type="submit">儲存</button>
            </div>
        </form>
    </div>

@endsection
@push('scripts')
    <script>
        let hideAlert = function(){
            $('.alert').prop('hidden', true);
        }

        $(function () {
            $('#profile-form').submit(function(e) {
                e.preventDefault();
                let inputData = new FormData(this);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('auth.password.reset') }}',
                    data: inputData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        if (typeof (data.error) !== 'undefined') {
                            let errors = Object.values(data.error)
                            $('#validator-alert').prop('hidden', false);
                            let validatorAlertUl = $('#validator-alert-ul');
                            validatorAlertUl.empty();
                            errors.forEach(i => validatorAlertUl.append($("<li></li>").text(i)));
                        } else {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: '密碼重置成功',
                                showConfirmButton: false,
                                timer: 1500
                            })
                            let doIt = function() {
                                window.location.assign('{{ route('login') }}');
                            }
                            setTimeout(doIt, 1500);
                        }
                    }
                });
            });
        });
    </script>
@endpush
