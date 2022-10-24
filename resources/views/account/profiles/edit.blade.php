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
            <form class="uk-form-stacked" id="profile-form">
                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="name">真實姓名(必填)</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-input" id="name" name="name" value="{{ $user->name }}" required>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="email">電子郵件(必填)</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" name="email" value="{{ $user->email }}">
                    </div>
                    @if($user->email_verified_at === null)
                        <a href="{{ route('account.profile_email.edit') }}" class="custom-link">驗證</a>
                    @endif
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="phone">手機號碼(必填)</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="tel" id="phone" name="phone"
                               pattern="[0-9]{3}[0-9]{3}[0-9]{4}" value="{{ $user->phone }}">
                    </div>
                    @if($user->phone_verified_at === null)
                        <a href="{{ route('account.profile_phone.edit') }}" class="custom-link">驗證</a>
                    @endif
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="birthday">生日(必填)</label>
                    <div class="uk-form-controls">
                        <input type="date" class="uk-input" name="birthday" value="{{ $user->birthday_format }}">
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label custom-font-medium" for="birthday">收件縣市、鄉鎮</label>
                    <div class="uk-grid-small uk-child-width-1-3 uk-form-controls twzipcode" uk-grid>
                        <div data-role="county" data-style="uk-select" data-name="county"
                             data-value="{{ Auth::user()->county ?? null }}"></div>
                        <div data-role="district" data-style="uk-select" data-name="district"
                             data-value="{{ Auth::user()->district ?? null }}"></div>
                        <div data-role="zipcode" data-style="uk-select" data-name="zip_code"
                             data-value="{{ Auth::user()->zip_code ?? null }}"></div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label custom-font-medium" for="address">街道地址</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" id="address" name="address" placeholder="輸入您的地址"
                                   value="{{ Auth::user()->address ?? null }}" autocomplete="street-address">
                        </div>
                    </div>
                </div>

                <div class="uk-margin" id="warning-alert" hidden>
                    <div class="uk-alert-warning" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        <ul id="warning-alert-ul">

                        </ul>
                    </div>
                </div>

                <div class="uk-margin uk-flex uk-flex-right ">
                    <button class="uk-button custom-button-1 uk-width-auto@s" type="submit">儲存</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/jQuery-TWzipcode/twzipcode.js') }}"></script>
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
                        'css': 'uk-select',
                        'required': false,
                    },
                    'zipcode': {
                        'css': 'uk-input',
                        'readonly': true,
                        'required': false,
                    },
                }
            );

            $('#profile-form').submit(function(e) {
                e.preventDefault();
                let inputData = new FormData(this);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('account.profile.update') }}',
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
                                window.location.assign('{{ route('account.profile.edit') }}');
                            }
                            setTimeout(doIt, 1000);
                        }
                    }
                });
            });
        });
    </script>
@endpush
