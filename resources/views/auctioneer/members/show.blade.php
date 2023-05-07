@extends('layouts.auctioneer')
@inject('orderStatusPresenter', 'App\Presenters\OrderStatusPresenter')
@inject('userPresenter', 'App\Presenters\UserPresenter')
@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <a href="{{ route('auctioneer.members.index') }}" class="custom-link"> > 返回會員管理</a>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body">
            <h2 class="uk-card-title">會員資料</h2>
            <input id="user-id" value="{{ $user->id }}" hidden>
            <table class="uk-table uk-table-divider">
                <tbody>
                    <tr>
                        <td class="uk-table-expand">姓名</td>
                        <td class="uk-table-expand">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td class="uk-table-expand">電話</td>
                        <td class="uk-table-expand">{{ $user->phone }}</td>
                        <td class="uk-table-expand">信箱</td>
                        <td class="uk-table-expand">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td class="uk-table-expand">會員等級</td>
                        <td class="uk-table-expand">{{ $userPresenter->presentRole($user->role) }}</td>
                    </tr>
                    <tr>
                        <td class="uk-table-expand">會員狀態</td>
                        <td class="uk-table-expand">{{ $userPresenter->presentStatus($user->status) }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="uk-flex uk-flex-right">
                <div class="uk-grid-small" uk-grid>
                    @if($user->role == 3)
                        <div>

                            <a href="#confirm-role-upgrade" rel="modal:open" class="uk-button custom-button-1">升為高級會員</a>
                            <div id="confirm-role-upgrade" class="modal">
                                <h2>確認升為高級會員嗎？</h2>
                                <p class="uk-text-right">
                                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                    <a class="uk-button custom-button-1 uk-width-auto@s submit-buttons" id="role-upgrade">確認</a>
                                </p>
                            </div>

                        </div>
                    @elseif($user->role == 2)
                        <div>

                            <a href="#confirm-role-downgrade" rel="modal:open" class="uk-button custom-button-1">降為普通會員</a>
                            <div id="confirm-role-downgrade" class="modal">
                                <h2>確認降為普通會員嗎？</h2>
                                <p class="uk-text-right">
                                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                    <a class="uk-button custom-button-1 uk-width-auto@s submit-buttons" id="role-downgrade">確認</a>
                                </p>
                            </div>

                        </div>
                    @endif

                    @if($user->status == 0 || $user->status == 2)
                        <div>

                            <a href="#confirm-block" rel="modal:open" class="uk-button custom-button-2">封鎖</a>
                            <div id="confirm-block" class="modal">
                                <h2>確認封鎖嗎？</h2>
                                <p class="uk-text-right">
                                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                    <a class="uk-button custom-button-1 uk-width-auto@s submit-buttons" id="block">確認</a>
                                </p>
                            </div>

                        </div>
                    @else
                        <div>

                            <a href="#confirm-unblock" rel="modal:open" class="uk-button custom-button-2">解除封鎖</a>
                            <div id="confirm-unblock" class="modal">
                                <h2>確認解除封鎖嗎？</h2>
                                <p class="uk-text-right">
                                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                    <a class="uk-button custom-button-1 uk-width-auto@s submit-buttons" id="unblock">確認</a>
                                </p>
                            </div>

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="uk-margin">
        <div class="uk-card uk-card-default uk-card-body">
            <h2 class="uk-card-title">訂單記錄</h2>
            <table class="uk-table uk-table-divider">
                <thead>
                <tr>
                    <th>編號</th>
                    <th>物品名稱</th>
                    <th>成交價格</th>
                    <th>訂單狀態</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($user->orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->lot->name }}</td>
                            <td>NT${{ number_format($order->total) }}</td>
                            <td>{{ $orderStatusPresenter->present($order) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
            $('.submit-buttons').click(function () {
                let userId = $('#user-id').val();
                let action = $(this).attr('id');
                let url, data;
                switch (action) {
                    case 'role-upgrade':
                        url = '{{ route("ajax.members.role-upgrade", ":id") }}';
                        url = url.replace(':id', userId);
                        data = {userId: userId};
                        break;
                    case 'role-downgrade':
                        url = '{{ route("ajax.members.role-downgrade", ":id") }}';
                        url = url.replace(':id', userId);
                        data = {userId: userId};
                        break;
                    case 'block':
                        url = '{{ route("ajax.members.block", ":id") }}';
                        url = url.replace(':id', userId);
                        data = {userId: userId};
                        break;
                    case 'unblock':
                        url = '{{ route("ajax.members.unblock", ":id") }}';
                        url = url.replace(':id', userId);
                        data = {userId: userId};
                        break;
                }

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: url,
                    data: data,
                    success: function () {
                        window.location.reload();
                    }
                });
            });

        });
    </script>
@endpush
