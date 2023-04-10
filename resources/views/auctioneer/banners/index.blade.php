@extends('layouts.auctioneer')

@section('content')
    @if (session('notification'))
        <script>
            $(function () {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: '{{ session('notification') }}',
                    showConfirmButton: false,
                    timer: 1500
                })
            });
        </script>
    @endif
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <form method="post" action="{{ route('auctioneer.banner_indexes.update') }}">
            @csrf
            <div class="uk-card uk-card-default uk-card-body">
                <h3 class="uk-card-title uk-form-label">Banner 順序</h3>
                <div class="uk-margin">
                    <ul class="uk-grid-small uk-child-width-1-2 uk-child-width-1-4@s" uk-sortable="handle: .uk-card" uk-grid>
                        @foreach($banners as $banner)
                            <li>
                                <div class="uk-card uk-card-default uk-card-body uk-text-center"
                                     style="background-image: url('{{ $banner->image->url }}'); background-position: center; background-repeat: no-repeat; background-size: cover; position: relative;">
                                    <a aria-label="Close" uk-close style="position: absolute; bottom: 80%; left: 90%; height: 30px; line-height: 30px; width: 30px; border-radius: 50%; background-color: #d62828; color: white; text-align: center; cursor: pointer;" class="delete-buttons" banner-id="{{ $banner->id }}"></a>
                                    <input type="text" name="ids[]" value="{{ $banner->id }}" hidden>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="uk-margin">
                    <div class="uk-flex uk-flex-right">
                        <button class="uk-button custom-button-1">保存</button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="uk-margin">
        <form method="post" action="{{ route('auctioneer.banners.create') }}" enctype="multipart/form-data">
            @csrf
            <div class="uk-card uk-card-default uk-card-body">
                <h3 class="uk-card-title uk-form-label">上傳Banner圖片 (1920x1080)</h3>
                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">Banner 內容</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="form-stacked-text" type="text" name="slogan" required>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-margin" id="output-section" hidden>
                        <div class="uk-flex uk-flex-center">
                            <div>
                                <figure class="uk-box-shadow-medium">
                                    <img class="image-no-known" id="output" uk-img width="960px" height="540px">
                                </figure>
                            </div>

                        </div>
                    </div>
                    <div uk-grid>
                        <div class="uk-flex uk-flex-left uk-width-expand">
                            <div class="js-upload" uk-form-custom>
                                <input type="file" accept="image/*" onchange="loadFile(event)" name="image" required>
                                <button class="uk-button uk-button-default" type="button" tabindex="-1">選擇圖片</button>
                            </div>
                        </div>
                        <div class="uk-flex uk-flex-right">
                            <div>
                                <button class="uk-button custom-button-1">上傳圖片</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        var loadFile = function(event) {
            let outputSection = $("#output-section");
            outputSection.prop("hidden", false);
            var output = document.getElementById('output');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            }
        };
    </script>
    <script>
        $(function () {
            $('.delete-buttons').click(function() {
                let bannerId = $(this).attr('banner-id');
                let url = '{{ route("auctioneer.banner.delete", ":id") }}';
                url = url.replace(':id', bannerId);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: url,
                    success: function (response) {
                        window.location.reload();
                    },
                    error: function (response) {

                    }
                });
            });
        });
    </script>
@endpush
