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
                <div class="uk-width-1-1">
                    <div class="uk-margin">
                        <div class="uk-card uk-card-default uk-card-body">
                            <h3 class="uk-card-title">Google</h3>
                            @if($user->googleBindStatus)
                                <p>已綁定</p>
                            @else
                                <p>綁定 Google 可以進行快速登入。按此 ”<a href="{{ route('auth.google.handle') }}" class="custom-link">綁定</a>“ 進行 Google 綁定</p>
                            @endif
                        </div>
                    </div>
                    <div class="uk-margin">
                        <div class="uk-card uk-card-default uk-card-body">
                            <h3 class="uk-card-title">LINE</h3>
                            @if($user->lineBindStatus)
                                <div>
                                    已綁定
                                </div>
                            @else
                                <div id="unbind-section">
                                    <label>
                                        綁定 LINE 即可體驗下列功能：
                                    </label>
                                    <ul class="uk-list uk-list-disc">
                                        <li>
                                            進行快速登入
                                        </li>
                                        <li>
                                            在 LINE 上進行競標
                                        </li>
                                        <li>
                                            拍賣會開始時通知您
                                        </li>
                                    </ul>
                                    <br>
                                    <label>步驟：</label>
                                    <ul class="uk-list uk-list-decimal">
                                        <li>
                                            加入官方帳號，按此 ”<a href="https://line.me/R/ti/p/@164mbqgn" class="custom-link">加入好友</a>“，或是掃描下方 QR Code  將官方帳號加入好友<br>
                                            <img width="100px" style="margin-bottom:3px; margin-right:5px;" src="{{ asset('images/web/common/test-line.png') }}" />
                                        </li>
                                        <li>
                                            按此 ”<a id="generate-line-verify-code" class="custom-link">產生</a>“ 產生 LINE 驗證碼<br>
                                            <span id="verify-code" class="custom-font-medium"></span>
                                        </li>
                                        <li>
                                            在 LINE 聊天室選單中選取綁定帳號，我們將會傳一個連結給您，點開連結並輸入上方產生的驗證碼進行綁定。
                                        </li>

                                    </ul>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        Echo.private(`users.{{ $user->id }}`)
            .listen('LineBindSuccess', (e) => {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'LINE 綁定成功',
                    text: '您現在可以關閉手機上 LINE 的頁面',
                    showConfirmButton: false,
                    timer: 3000
                });
                let unbindSection = $('#unbind-section');
                unbindSection.empty();
                unbindSection.append('<p>已綁定</p>');
            });
    </script>
    <script>
        $(function () {
            $('#generate-line-verify-code').click(function() {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('auth.line_verify_code.generate') }}',
                    type: "get",
                    success: function (code) {
                        $('#verify-code').text('驗證碼：'+code);
                    }
                });
            });
        });
    </script>
    @if (session('success'))
        <script>
            $(function () {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: '{{session('success')}}',
                    showConfirmButton: false,
                    timer: 1500
                })
            });
        </script>
    @elseif(session('warning'))
        <script>
            $(function () {
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: '{{session('warning')}}',
                    showConfirmButton: false,
                    timer: 1500
                })
            });
        </script>
    @endif
@endpush
