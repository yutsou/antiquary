@extends('layouts.member')

@section('content')
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-3@s">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">註冊</h1>
            </div>
            <form class="uk-form-stacked" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="uk-margin">
                    <label class="uk-form-label" for="name">真實姓名</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-input" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="email">電子郵件</label>
                    <div class="uk-form-controls">
                        <input type="email" class="uk-input" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                </div>

                @if ($errors->has('email'))
                    <div class="uk-alert" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        @foreach ($errors->get('email') as $error)
                            <p class="custom-color-2">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="uk-margin">
                    <label class="uk-form-label" for="phone">電話號碼</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="tel" id="phone" name="phone"
                               pattern="[0-9]{3}[0-9]{3}[0-9]{4}" required>
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
                    <label class="uk-form-label" for="password">再確認一次密碼</label>
                    <div class="uk-form-controls">
                        <input type="password" class="uk-input" id="password_confirmation" name="password_confirmation"
                               required autocomplete="new-password">
                    </div>
                </div>

                @if ($errors->has('password'))
                    <div class="uk-alert" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        @foreach ($errors->get('password') as $error)
                            <p class="custom-color-2">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="uk-margin">
                    <button type="submit" class="uk-button custom-button-1 uk-width-1-1">註冊</button>
                </div>
            </form>
        </div>
    </div>
@endsection
