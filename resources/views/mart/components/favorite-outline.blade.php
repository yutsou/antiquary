<div id="un-login-favorite-notice" class="modal">
    <p class="custom-font-medium">物品加入追蹤清單前需要先登入。</p>
    <div class="uk-flex uk-flex-right">
        <a class="uk-button custom-button-1"
           href="{{ route('login.show', ['redirectUrl'=>str_replace('/','_',request()->path()) ]) }}">登入</a>
    </div>
</div>
@push('style')
    <link rel="stylesheet" href="{{ asset('extensions/jquery-modal/0.9.2/css/jquery.modal.min.css') }}" crossorigin="anonymous">
@endpush
@push('scripts')
    <script>
        $(function () {
            let addFavorite = function (lotId) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '/account/ajax/lots/' + lotId + '/favorite',
                    data: {lotId: lotId},
                    success: function (status) {
                        if (status === 'added') {
                            $('#favoriteStatus-' + lotId).removeClass('google-icon').addClass('google-icon-fill');
                        } else {
                            $('#favoriteStatus-' + lotId).removeClass('google-icon-fill').addClass('google-icon');

                        }
                    }
                });
            };

            let stopBubbling =  function(e) {
                if (e && e.stopPropagation) {
                    e.stopPropagation();      //阻止事件 冒泡传播
                } else {
                    e.cancelBubble = true;   //ie兼容
                }
            }

            $(".favorite").click(function (event) {
                addFavorite($(this).attr('lotId'));
                stopBubbling(event);
            });


            $(".un-login-favorite").click(function (event){
                $('#un-login-favorite-notice').modal();
                stopBubbling(event);
            });
        });
    </script>
@endpush
