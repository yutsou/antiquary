@extends('layouts.expert')
@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-alert-warning" id="validator-alert" uk-alert hidden>
        <ul id="validator-alert-ul"></ul>
    </div>
    <form class="uk-form-stacked" id="auction-form">
        @csrf
        <ul class="uk-child-width-expand@s" uk-tab>
            <li class="uk-active"><a href="#">拍賣會資訊</a></li>
            <li><a href="#">拍賣會商品</a></li>
        </ul>
        <ul class="uk-switcher uk-margin">
            <li>
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title uk-form-label">拍賣會名稱</h3>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: info"></span>
                            <input class="uk-input" id="auctionName" name="name" type="text">
                        </div>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title uk-form-label">拍賣會時間</h3>

                        <div class="uk-margin uk-form-horizontal">
                            <label class="uk-form-label">開始時間</label>
                            <div class="uk-form-controls">
                                <input class="uk-input uk-width-auto" id="auctionStartAt" type="datetime-local" name="auction_start_at">
                                <label><span uk-icon="icon: arrow-left" class="uk-margin-left"></span>點選日曆圖示選擇日期</label>
                            </div>
                        </div>

                        <div class="uk-margin uk-form-horizontal">
                            <label class="uk-form-label">結束時間</label>
                            <div class="uk-form-controls">
                                <input class="uk-input uk-width-auto" id="auctionEndAt" type="datetime-local" name="auction_end_at">
                                <label><span uk-icon="icon: arrow-left" class="uk-margin-left"></span>點選日曆圖示選擇日期</label>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <div class="uk-margin-medium">
                    <table id="lotDatas" class="uk-table uk-table-striped uk-width-1-1" style="width: 100% !important;">
                        <thead>
                        <tr>
                            <th>選擇</th>
                            <th>ID</th>
                            <th>規格</th>
                            <th>狀態</th>
                        </tr>
                        </thead>
                    </table>
                </div>

            </li>
        </ul>
        <div class="uk-margin uk-align-right">
            <button type="submit" class="uk-button custom-button-1">建立拍賣會</button>
        </div>
    </form>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/datatables-1.11.5/css/dataTables.uikit.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script defer src="{{ asset('extensions/datatables-1.11.5/js/jquery.dataTables.min.js') }}" crossorigin="anonymous"></script>
    <script defer src="{{ asset('extensions/datatables-1.11.5/js/dataTables.uikit.min.js') }}" crossorigin="anonymous"></script>
    <script defer>
        $(function () {
            //datatable設定
            $('#lotDatas').DataTable({
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
                "ajax": "{{ route('expert.ajax.auction-lot.get', $mainCategoryId) }}",
                "columns":
                    [
                        { "data": "selection", "orderable": false},
                        { "data": "id", "orderable": false},
                        { "data": "specification", "orderable": false},
                        { "data": "status", "orderable": false},
                    ]
            });
        });
        $(function () {
            $('#auction-form').submit(function(e) {
                e.preventDefault();
                let inputData = new FormData(this);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('expert.auctions.store', ['mainCategoryId'=>$mainCategoryId]) }}',
                    data: inputData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        if (typeof (data.error) !== 'undefined') {
                            let errors = Object.values(data.error)
                            $('#validator-alert').prop('hidden', false);
                            let validatorAlertUl = $('#validator-alert-ul');
                            validatorAlertUl.empty();
                            errors.forEach(i => validatorAlertUl.append($("<li></li>").text(i)));
                            $('html,body').animate({scrollTop: 0}, 500);
                        } else {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: '拍賣會已建立',
                                showConfirmButton: false,
                                timer: 1000
                            })

                            let doIt = function() {
                                window.location.assign('{{ route('expert.auctions.show', $mainCategoryId) }}');
                            }
                            setTimeout(doIt, 1000);
                        }
                    }
                });
            });
        });
    </script>
@endpush
