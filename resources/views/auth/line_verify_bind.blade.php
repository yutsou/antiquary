@extends('layouts.member')

@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>

    <form class="uk-form-stacked" method="POST" action="{{ route('auth.line.bind') }}">
        @csrf
        <input type="hidden" name="linkToken" value="{{ request()->get('linkToken') ?? '' }}">
        <label>驗證碼
            <input type="text" name="bind_verify_code" class="uk-input">
        </label>
        @if($errors->any())
            <div class="uk-margin">
                <div class="uk-alert-warning alert" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p>{{ $errors->first() }}</p>
                </div>
            </div>
        @endif
        <div class="uk-margin">
            <button type="submit" class="uk-button custom-button-1">綁定</button>
        </div>
    </form>
@endsection
