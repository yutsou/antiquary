@extends('layouts.auctioneer')

@section('content')
<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">管理員中心</h1>
</div>
@if (session('notification'))
    <script>
        $(function () {
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: '{{ session('notification') }}',
                showConfirmButton: false,
                timer: 1500
            })
        });
    </script>
@endif
@inject('auctioneerPresenter', 'App\Presenters\AuctioneerPresenter')
<form class="uk-form-stacked" method="POST" action="{{ route('auctioneer.experts.update', ['userId'=> $expert->id]) }}">
    @csrf
    <div class="uk-margin">
        <label class="uk-form-label" for="name">專家名稱</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: user"></span>
            <input type="text" class="uk-input uk-form-width-large" id="name" name="name" value="{{ $expert->name }}" required>
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="email">電子郵件</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: mail"></span>
            <input type="email" class="uk-input uk-form-width-large" id="email" name="email" value="{{ $expert->email }}" required>
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
        <label class="uk-form-label" for="password">密碼</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: lock"></span>
            <input type="password" class="uk-input uk-form-width-large" id="password" name="password" autocomplete="new-password">
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="password">再確認一次密碼</label>
        <div class="uk-inline uk-form-controls">
            <span class="uk-form-icon" uk-icon="icon: lock"></span>
            <input type="password" class="uk-input uk-form-width-large" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
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
        <label class="uk-form-label">設定專家主分類</label>
        <div class="uk-grid-small uk-child-width-auto uk-grid">
            @foreach($mainCategories as $mainCategory)
                {!! $auctioneerPresenter->checked($expert ,$mainCategory) !!}
            @endforeach
        </div>
    </div>



    <div class="uk-margin">
        <button type="submit" class="uk-button custom-color-group-1">儲存</button>
    </div>
</form>
@endsection
