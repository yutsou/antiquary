@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <table class="uk-table uk-table-striped uk-width-1-3@m">
            <thead>
            <tr>
                <th>價格區間</th>
                <th>增額</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>NT$1~500</td>
                <td>+NT$50</td>
            </tr>
            <tr>
                <td>NT$501~5,000</td>
                <td>+NT$250</td>
            </tr>
            <tr>
                <td>NT$5,001~10,000</td>
                <td>+NT$500</td>
            </tr>
            <tr>
                <td>NT$10,001~25,000</td>
                <td>+NT$2,500</td>
            </tr>
            <tr>
                <td>NT$25,001~50,000</td>
                <td>+NT$5,000</td>
            </tr>
            <tr>
                <td>NT$50,001~250,000</td>
                <td>+NT$10,000</td>
            </tr>
            <tr>
                <td>NT$250,001~1,000,000</td>
                <td>+NT$50,000</td>
            </tr>
            <tr>
                <td>NT$1,000,001~</td>
                <td>+NT$100,000</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection


