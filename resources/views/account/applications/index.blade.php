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
@push('scripts')
    <script>
        $(function () {
            $('.application-logistic-info').on('click', function() {
                let lotId = $(this).attr('lotId');
                let url = '{{ route("account.application_logistic_info.create", ":id") }}';
                url = url.replace(':id', lotId);
                window.location.assign(url);
                return false;
            });

        });
    </script>
@endpush
