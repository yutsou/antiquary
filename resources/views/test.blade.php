@extends('layouts.member')

@section('content')
    <div id="time" uk-countdown="date: 2021-11-11T10:45:51+00:00">
        <span class="uk-countdown-number uk-countdown-days"></span>
        <span class="uk-countdown-separator">:</span>
        <span class="uk-countdown-number uk-countdown-hours"></span>
        <span class="uk-countdown-separator">:</span>
        <span class="uk-countdown-number uk-countdown-minutes"></span>
        <span class="uk-countdown-separator">:</span>
        <span class="uk-countdown-number uk-countdown-seconds"></span>
    </div>
    <a id="test">Test</a>
@endsection
@push('scripts')
    <script>
        $(function () {
            $('#test').click(function () {
                //var notifications = UIkit.notification('MyMessage', 'danger');
                $('#time').attr('uk-countdown', 'date: 2021-11-05T10:45:51+00:00');
            });
        });
    </script>
@endpush
