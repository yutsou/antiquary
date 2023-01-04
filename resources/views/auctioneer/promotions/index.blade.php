@extends('layouts.auctioneer')

@section('content')
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>

    <div class="uk-margin">
        <form id="edit-form">
            <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
                <label><input class="uk-checkbox" type="checkbox" id="promotion-status" name="status" {{ $promotions[0] == true ? 'checked' : '' }}> 啟動優惠</label>
            </div>
            <div id="input-section" {{ $promotions[0] == true ? "" : "hidden" }}>
                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">賣家佣金抽成</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" name="commission_rate" type="number" placeholder="輸入整數為金額、小數為趴數" value="{{ $promotions[1] }}">
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">買家額外費用</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" name="premium_rate" type="number" placeholder="輸入整數為金額、小數為趴數" value="{{ $promotions[2] }}">
                    </div>
                </div>
            </div>
            <div class="uk-margin">
                <a class="uk-button custom-button-1" id="update-button">儲存</a>
            </div>
        </form>

    </div>

@endsection
@push('scripts')
    <script>
        $(function () {
            $('#promotion-status').click(function(){
                let status = $(this).prop('checked');
                if(status === true) {
                    $('#input-section').prop('hidden', false);
                } else {
                    $('#input-section').prop('hidden', true);
                }
            });
            $('#update-button').click(function() {
                let url = '{{ route("auctioneer.promotions.update") }}';
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    data:$('#edit-form').serialize(),
                    url: url,
                    success: function (response) {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: '儲存成功',
                            showConfirmButton: false,
                        })

                        function successAction (response) {
                            window.location.replace(response.success);
                        }

                        setTimeout(function (){
                            successAction(response)
                        }, 1500);
                    },
                    error: function (response) {
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            html:
                                merge_errors(response.responseJSON.errors),
                            showConfirmButton: true,
                            confirmButtonColor: '#003a6c',
                            confirmButtonText: '<span style="color: #fff;">確定</span>'
                        })
                    }
                });
            });
        });
    </script>
@endpush
