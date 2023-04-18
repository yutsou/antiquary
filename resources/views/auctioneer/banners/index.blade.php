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
                        @if($banners->count() != 0)
                            @foreach($banners as $banner)
                                <li>
                                    <div class="uk-card uk-card-default uk-card-body uk-text-center"
                                         style="background-image: url('{{ $banner->desktop_banner->url }}'); background-position: center; background-repeat: no-repeat; background-size: cover; position: relative;">
                                        <a aria-label="Close" uk-close style="position: absolute; bottom: 80%; left: 90%; height: 30px; line-height: 30px; width: 30px; border-radius: 50%; background-color: #d62828; color: white; text-align: center; cursor: pointer;" class="delete-buttons" banner-id="{{ $banner->id }}"></a>
                                        <input type="text" name="ids[]" value="{{ $banner->id }}" hidden>
                                    </div>
                                </li>
                            @endforeach
                        @endif
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
                <h3 class="uk-card-title uk-form-label">製作Banner</h3>
                <div class="uk-margin">
                    <label class="uk-form-label" for="banner-content">Banner 內容</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="banner-content" type="text" name="slogan">
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="banner-link">Banner 鏈結</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="banner-link" type="text" name="link">
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="desktop-banner">桌面版 Banner (1920x1080)</label>
                        <div uk-form-custom>
                            <input class="image-inputs" platform="desktop" type="file" accept="image/*" name="desktopBanner" required>
                            <button class="uk-button uk-button-default" type="button" tabindex="-1">選擇圖片</button>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <div class="uk-margin" id="desktop-output-section" hidden>
                            <div class="uk-flex uk-flex-center">
                                <div>
                                    <figure class="uk-box-shadow-medium">
                                        <img class="image-no-known" id="desktop-output" uk-img width="960px" height="540px">
                                    </figure>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="mobile-banner">手機版 Banner (1080x1080)</label>
                        <div uk-form-custom>
                            <input class="image-inputs" platform="mobile" type="file" accept="image/*" name="mobileBanner" required>
                            <button class="uk-button uk-button-default" type="button" tabindex="-1">選擇圖片</button>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <div class="uk-margin" id="mobile-output-section" hidden>
                            <div class="uk-flex uk-flex-center">
                                <div>
                                    <figure class="uk-box-shadow-medium">
                                        <img class="image-no-known" id="mobile-output" uk-img width="960px" height="540px">
                                    </figure>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-flex uk-flex-right">
                        <button class="uk-button custom-button-1">製作</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        let loadImage = function(event) {
            let outputSection = $("#d-output-section");
            outputSection.prop("hidden", false);

            let output = $('#d-output');
            output.attr('src', URL.createObjectURL(event.target.files[0]))

            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            }
        };
    </script>
    <script>
        $(function () {
            $('.image-inputs').on('change', function() {
                let platform = $(this).attr('platform');
                let outputSection = $("#"+platform+"-output-section");
                outputSection.prop("hidden", false);

                let output = $("#"+platform+"-output");
                output.attr('src', URL.createObjectURL(event.target.files[0]))

                output.onload = function() {
                    URL.revokeObjectURL(output.src) // free memory
                }
            });
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
