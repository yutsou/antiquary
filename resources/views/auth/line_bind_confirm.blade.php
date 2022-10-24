@extends('layouts.member')

@section('content')
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-2@s">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">帳戶設定</h1>
            </div>
            <a href="{{ $link }}" class="uk-button custom-button-1">綁定確認</a>
        </div>
    </div>
@endsection
