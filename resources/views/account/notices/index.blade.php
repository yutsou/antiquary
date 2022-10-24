@extends('layouts.member')
@inject('noticePresenter', 'App\Presenters\NoticePresenter')
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
                <div class="uk-grid-match uk-child-width-1-1 uk-grid-small" uk-grid>
                    @foreach($notices as $notice)
                        <div>
                            {!! $noticePresenter->transferNotice($notice)!!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
