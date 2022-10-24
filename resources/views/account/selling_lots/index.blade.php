@extends('layouts.member')
@inject('memberLotIndexPresenter', 'App\Presenters\MemberLotIndexPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-flex uk-flex-center">
        <div class="uk-width-1-1">
            <div class="uk-margin-medium">
                <h1 class="uk-heading-medium">{{ $head }}</h1>
            </div>
            <div>
                @foreach($lots as $lot)
                    {!! $memberLotIndexPresenter->present($lot) !!}
                @endforeach
            </div>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>

    <script>
        $(function () {
            $('.confirm-notice-arrival').on('click', function() {
                let lotId = $(this).attr('lotId');
                $('#confirm-notice-arrival').modal();
                return false;
            });

            $('.unsold-lot-process').on('click', function() {
                let lotId = $(this).attr('lotId');
                let url = '{{ route("account.unsold_lots.edit", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
                return false;
            });

            $('.show-shipping-info').on('click', function() {
                let orderId = $(this).attr('orderId');
                let url = '{{ route("account.orders.show_shipping_info", ":id") }}';
                url = url.replace(':id', orderId);
                window.location.assign(url);
                return false;
            });

            $('.communication').on('click', function() {
                let orderId = $(this).attr('orderId');
                let url = '{{ route("mart.chatroom.show", ":id") }}';
                url = url.replace(':id', orderId);
                window.location.assign(url);
                return false;
            });


            $('#close-modal').on('click', function(){
                $.modal.close();
            });
        });
    </script>
@endpush
