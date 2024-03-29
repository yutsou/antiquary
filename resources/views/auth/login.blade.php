@extends('layouts.member')

@section('content')
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-3@s">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            <form class="uk-form-stacked" method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="redirectUrl" value="{{ $redirectUrl ?? '' }}">
                <input type="hidden" name="linkToken" value="{{ request()->get('linkToken') ?? '' }}">

                <div class="uk-margin">
                    <label class="uk-form-label" for="email">電子郵件</label>
                    <div class="uk-form-controls">
                        <input type="email" class="uk-input" id="email" name="email" value="{{ old('email') }}" required>
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
                    <a class="uk-link-text " href="{{ route('auth.password_forgot.show') }}">忘記密碼？</a>
                </div>
                <div class="uk-margin">
                    <button type="submit" class="uk-button custom-button-1 uk-width-1-1">登入</button>
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
