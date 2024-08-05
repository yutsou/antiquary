@extends('layouts.member')
@inject('orderStatusPresenter', 'App\Presenters\OrderStatusPresenter')
@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ route('dashboard') }}" class="custom-color-1 custom-link-mute">會員中心</a> > <a href="{{ route('account.orders.index') }}" class="custom-color-1 custom-link-mute">已得標的物品</a> > <a href="{{ route('account.orders.show', $order) }}" class="custom-color-1 custom-link-mute">訂單#{{ $order->id }}</a> > <a href="{{ route('mart.chatroom.show', $order) }}" class="custom-color-1 custom-link-mute">聊天室</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body" style="background-color: #F7F7F7;">
            <div class="uk-margin">

                <div class="uk-overflow-auto" id="message-window" style="height: 50vh;">
                    @foreach($order->messages as $message)
                        @if(Auth::user()->id === $message->user_id)
                            <div class="uk-flex uk-flex-right">
                                <div class="uk-margin uk-card uk-card-small uk-card-default uk-card-body uk-width-auto">{{ $message->message }}</div>
                            </div>
                        @else
                            <div class="uk-flex uk-flex-left">
                                <div class="uk-margin uk-card uk-card-small uk-card-default uk-card-body uk-width-auto">{{ $message->message }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>
            <div class="uk-margin">
                <div class="uk-grid-small" uk-grid>
                    <div class="uk-width-expand@s">
                        <textarea class="uk-textarea" rows="2" id="input"></textarea>
                    </div>
                    <div class="uk-width-auto@s">
                        <a class="uk-button custom-button-1 uk-width-auto@s" id="send-message">傳送</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        let newMessage = function (type, message) {
            let messageWindow = $('#message-window');
            let element = '';
            if (type === 0) {//0self, 1other
                element += '<div class="uk-flex uk-flex-right">';
            } else {
                element += '<div class="uk-flex uk-flex-left">';
            }
            element += '<div class="uk-margin uk-card uk-card-small uk-card-default uk-card-body uk-width-auto">' +
                message +
                '</div>' +
                '</div>';
            messageWindow.append(element);
            messageWindow.scrollTop(messageWindow[0].scrollHeight);
        }

        let sendMessage = function (orderId) {
            let input = $('#input');
            let message = input.val();

            axios.post('/orders/' + orderId + '/messages', {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'message': message,
            })
            .then(function (response) {
                input.val('');
                newMessage(0, message);
            })
            .catch(function (error) {

            });
        };

        let haveRead = function (messageId) {

            axios.post('/messages/' + messageId + '/haveRead', {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'messageId': messageId,
            })
                .then(function (response) {

                })
                .catch(function (error) {

                });
        }

        $(function () {
            let messageWindow = $('#message-window');
            messageWindow.scrollTop(messageWindow[0].scrollHeight);

            $("#send-message").click(function () {
                sendMessage({{ $order->id }});
            });
        });

        $(document).on('keypress', function (e) {
            if (e.which === 13) {//Enter key
                sendMessage({{ $order->id }});
            }
        });

        Echo.private(`orders.{{ $order->id }}`)
            .listen('NewMessage', (e) => {
                newMessage(1, e.message);
                haveRead(e.messageId);
            });
    </script>
@endpush
