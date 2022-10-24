@extends('layouts.auctioneer')
@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <table id="orderData" class="uk-table uk-table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>規格</th>
            <th class="uk-table-expand">狀態</th>
            <th>動作</th>
        </tr>
        </thead>
    </table>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/datatables-1.11.5/css/dataTables.uikit.min.css') }}" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script src="{{ asset('extensions/datatables-1.11.5/js/jquery.dataTables.min.js') }}" crossorigin="anonymous"></script>
    <script src="{{ asset('extensions/datatables-1.11.5/js/dataTables.uikit.min.js') }}" crossorigin="anonymous"></script>
    <script>
        $(function () {
            //datatable設定
            $('#orderData').DataTable({
                "order": [],//取消datatable第一欄預設sort
                "language": {
                    "processing":   "處理中...",
                    "loadingRecords": "載入中...",
                    "lengthMenu":   "顯示 _MENU_ 項結果",
                    "zeroRecords":  "沒有符合的結果",
                    "info":         "顯示第 _START_ 至 _END_ 項結果，共 _TOTAL_ 項",
                    "infoEmpty":    "顯示第 0 至 0 項結果，共 0 項",
                    "infoFiltered": "(從 _MAX_ 項結果中過濾)",
                    "infoPostFix":  "",
                    "search":       "搜尋:",
                    "paginate": {
                        "first":    "第一頁",
                        "previous": "上一頁",
                        "next":     "下一頁",
                        "last":     "最後一頁"
                    },
                    "aria": {
                        "sortAscending":  ": 升冪排列",
                        "sortDescending": ": 降冪排列"
                    }
                },
                "ajax": "{{ url('auctioneer/ajax/orders') }}",
                "columns":
                    [
                        { "data": "id", "orderable": false},
                        { "data": "name", "orderable": false},
                        { "data": "order_status", "orderable": false},
                        { "data": "action", "orderable": false},
                    ]
            });
        });
    </script>
    <script src="{{ asset('js/orderAction.js') }}?v=03"></script>
    <script src="{{ asset('extensions/jquery-modal/0.9.2/js/jquery.modal.min.js') }}"></script>
@endpush
