<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<html>
    <head>
        <title>{{ $title }}</title>
        <meta {{ $version = '04' }}>
        <!-- Meta -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('extensions/uikit-3.15.19/css/uikit.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{$version}}" />
        <link href="{{ asset('extensions/sweetalert2/css/material-ui.min.css') }}" rel="stylesheet">
        @stack('style')

        <!-- Scripts -->
        <script src="{{ asset('js/jquery-3.6.0/jquery.min.js') }}"></script>
        <script src="{{ asset('extensions/uikit-3.15.19/js/uikit.min.js') }}"></script>
        <script src="{{ asset('extensions/uikit-3.15.19/js/uikit-icons.min.js') }}"></script>
        <script src="{{ asset('extensions/sweetalert2/js/sweetalert2.min.js') }}"></script>
        @stack('scripts')
    </head>
    <body>
        <div uk-grid uk-height-viewport="offset-top: true">
            <div class="uk-width-1-6 uk-background-secondary">
                <div>
                    <div class="uk-height-small uk-flex uk-flex-center uk-flex-middle custom-color-group-1" style="font-size: 1.6em">專家中心</div>
                </div>
                <ul class="uk-nav uk-nav-default uk-margin-left uk-margin-right uk-text-default" uk-nav>
                    <li class="uk-nav-header uk-margin-top" style="color: white; font-size: 1.2em;"><a href="{{ route('expert.dashboard') }}">專家中心</a></li>
                    <li class="uk-nav-divider"></li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <li class="uk-margin-top">
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">登出</a>
                        </li>
                    </form>
                </ul>
            </div>
            <div class="uk-width-5-6">
                <div class="uk-section">
                    <div class="uk-container">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
