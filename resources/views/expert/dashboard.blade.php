@extends('layouts.expert')
@inject('expertDashboardPresenter', 'App\Presenters\ExpertDashboardPresenter')
@section('content')
<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">專家中心</h1>
</div>
<div class="uk-grid-column-small uk-grid-row-large uk-child-width-1-4@s uk-text-center" uk-grid>
    @foreach ($domains as $domain)
    <div>
        <div class="uk-card" style="background-color: {{ $domain->category->color_hex }}">
            <div class="uk-card-media-top">
                <img src="{{ $domain->category->image->url }}" alt="">
            </div>
            <div class="uk-card-body">
                <h3 class="uk-card-title" style="color: white;">{{ $domain->category->name }}</h3>
                <ul class="uk-list uk-link-text">
                    <li><hr></li>
                    <li><a href=" {{ route('expert.default_specification_titles.manage', ['mainCategoryId'=>$domain->category->id]) }} " style="color: white;">管理預設規格</a></li>
                    <li><hr></li>
                    <li><a href="{{ route('expert.sub_categories.index', ['mainCategoryId'=>$domain->category->id]) }}" style="color: white;">子分類管理</a></li>
                    <li><hr></li>
                    <li><a href="{{ route('expert.lots.index', ['mainCategoryId'=>$domain->category->id]) }}" style="color: white;">商品管理</a>
                        {!! $expertDashboardPresenter->present($domain) !!}
                    </li>
                    <li><hr></li>
                    <li><a href="{{ route('expert.auctions.show', ['mainCategoryId'=>$domain->category->id])  }}" style="color: white;">拍賣會管理</a></li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
