@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.profile.edit') }}" class="custom-color-1 custom-link-mute">帳戶設定</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-3@s">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            <div class="uk-margin">
                <div class="custom-font-medium">{{ $user->phone }}</div>
                <a id="send-verify-code" class="custom-link">寄出驗證碼</a>
            </div>

            <div class="uk-margin">
                <div class="uk-alert-primary alert" uk-alert id="send-success-alert" hidden>
                    <a class="uk-alert-close" uk-close></a>
                    <p>驗證碼已寄至{{ $user->phone }}，請在30分鐘內進行驗證，若沒收到驗證碼，請於1分鐘後再次點選上方的 "寄出驗證碼"。</p>
                </div>
            </div>


            <form class="uk-form-stacked" id="verify-form" hidden>
                <label class="uk-form-label custom-font-medium" for="verify-code">驗證碼</label>
                <div class="uk-form-controls">
                    <input type="text" class="uk-input" id="inputCode" required>
                </div>
                <div class="uk-margin uk-flex uk-flex-left">
                    <a class="uk-button custom-button-1" id="code-verify">驗證</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let hideAlert = function(){
            $('.alert').prop('hidden', true);
        }
        $(function () {
            $('#code-verify').click(function(){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('account.code.verify') }}',
                    type: "post",
                    data:{
                        type:'phone',
                        inputCode: $('#inputCode').val()
                    },
                    success: function () {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: '手機驗證成功',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        let doIt = function() {
                            window.location.assign('{{ route('account.profile.edit') }}');
                        }
                        setTimeout(doIt, 1500);
                    },
                    error: function (result) {
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: result.responseJSON.error,
                            showConfirmButton: false,
                            timer: 1500
                        })
                    }
                });
            });
            $('#send-verify-code').click(function() {
                $('#verify-form').prop('hidden', false);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('account.verify_code.send') }}',
                    type: "post",
                    data:{
                        type:'phone',
                    },
                    success: function () {
                        $('#send-success-alert').prop('hidden', false);
                        setTimeout(hideAlert, 10000);
                    },
                    error: function (result) {
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: result.responseJSON.error,
                            showConfirmButton: false,
                            timer: 1500
                        })
                    }
                });
            });
        });
    </script>
@endpush
