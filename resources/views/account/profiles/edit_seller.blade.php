@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-2@s">

            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            <div class="uk-margin">
                <p>須完成以下資料才能進行委賣</p>
                <a href="{{ route('account.applications.create') }}" class="custom-link">前往委賣物品</a>
            </div>
            <form class="uk-form-stacked" id="profile-form">
                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="bank-name">受款銀行名稱(賣家必填)</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-input" id="bank-name" name="bank_name" value="{{ $user->bank_name }}" placeholder="xx銀行">
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="bank-branch-name">分行名稱(賣家必填)</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-input" id="bank-branch-name" name="bank_branch_name" value="{{ $user->bank_branch_name }}" placeholder="xx分行">
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="bank-account-name">戶名(賣家必填)</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-input" id="bank-account-name" name="bank_account_name" value="{{ $user->bank_account_name }}" placeholder="">
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="bank-account-number">帳號(賣家必填)</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-input" id="bank-account-number" name="bank_account_number" value="{{ $user->bank_account_number }}">
                    </div>
                </div>

                <div class="uk-margin" id="warning-alert" hidden>
                    <div class="uk-alert-warning" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        <ul id="warning-alert-ul">

                        </ul>
                    </div>
                </div>

                <div class="uk-margin uk-flex uk-flex-right">
                    <button class="uk-button custom-button-1 uk-width-auto@s" type="submit">儲存</button>
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

            $('#profile-form').submit(function(e) {
                e.preventDefault();
                let inputData = new FormData(this);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('account.seller.update') }}',
                    data: inputData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        if (typeof (data.error) !== 'undefined') {
                            let errors = Object.values(data.error)
                            $('#warning-alert').prop('hidden', false);
                            let validatorAlertUl = $('#warning-alert-ul');
                            validatorAlertUl.empty();
                            errors.forEach(i => validatorAlertUl.append($("<li></li>").text(i)));
                        } else {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: '資料已儲存',
                                showConfirmButton: false,
                                timer: 1000
                            })

                            let doIt = function() {
                                window.location.assign('{{ route('account.seller.edit') }}');
                            }
                            setTimeout(doIt, 1000);
                        }
                    }
                });
            });
        });
    </script>
@endpush
