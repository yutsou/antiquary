@extends('layouts.expert')

@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <a class="uk-button custom-button-1" href="{{ route('expert.auctions.create', ['mainCategoryId'=>$mainCategoryId]) }}">建立新拍賣會</a>
    </div>
    <div class="uk-margin">
        <table id="auctions-table" class="uk-table uk-table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>狀態</th>
                <th>開始時間</th>
                <th>預計結束時間</th>
            </tr>
            </thead>
        </table>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/datatables-1.11.5/css/dataTables.uikit.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script defer src="{{ asset('extensions/datatables-1.11.5/js/jquery.dataTables.min.js') }}" crossorigin="anonymous"></script>
    <script defer src="{{ asset('extensions/datatables-1.11.5/js/dataTables.uikit.min.js') }}" crossorigin="anonymous"></script>
    <script>
        $(function () {
            //datatable設定
            $('#auctions-table').DataTable({
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
                "ajax": "{{ route('expert.ajax.auction.get', $mainCategoryId) }}",
                "columns":
                    [
                        { "data": "id", "orderable": false},
                        { "data": "name", "orderable": false},
                        { "data": "status", "orderable": false},
                        { "data": "startAt", "orderable": false},
                        { "data": "expectEndAt", "orderable": false},
                    ]
            });
        });
    </script>
@endpush
