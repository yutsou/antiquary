@extends('layouts.member')

@section('content')
    <div class="uk-width-1-3 uk-align-center">
        <div class="uk-margin-medium">
            <h1 class="uk-heading-medium">{{ $head }}</h1>
        </div>
        <form class="uk-form-stacked" id="email-form">
            <div class="uk-margin">
                <label class="uk-form-label" for="email">電子郵件</label>
                <div class="uk-form-controls">
                    <input type="email" class="uk-input" id="email" name="email" required>
                </div>
            </div>
            <div class="uk-margin">
                <div class="uk-alert-warning alert" id="validator-alert" uk-alert hidden>
                    <ul id="validator-alert-ul"></ul>
                </div>
            </div>

            <div class="uk-margin uk-text-right">
                <button class="uk-button custom-button-1" type="submit">寄出</button>
            </div>

            @if ($errors->has('warning'))
                <div uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    @foreach ($errors->get('warning') as $error)
                        <p class="custom-color-2">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        let hideAlert = function(){
            $('.alert').prop('hidden', true);
        }

        $(function () {
            $('#email-form').submit(function(e) {
                e.preventDefault();
                let inputData = new FormData(this);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('auth.password_reset_confirm.send') }}',
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
                                title: '密碼重置信已寄出',
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
