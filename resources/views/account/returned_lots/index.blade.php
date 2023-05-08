@extends('layouts.member')
@inject('memberLotIndexPresenter', 'App\Presenters\MemberLotIndexPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
        <ul id="validator-alert-ul"></ul>
    </div>
    <div>
        @foreach($lots as $lot)
            {!! $memberLotIndexPresenter->present($lot) !!}
        @endforeach
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
    <script>
        $(function () {
            $('.returned-lot-logistic-info').on('click', function() {
                let lotId = $(this).attr('lotId');
                let url = '{{ route("account.returned_lots.index", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
                return false;
            });
        });
    </script>
@endpush
