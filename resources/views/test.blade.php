@extends('layouts.member')

@section('content')
    <div class="uk-container uk-margin-top">
        <h2 class="uk-heading-divider">圖片上傳並排序</h2>

        <form class="uk-form-stacked" id="lot-form">
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title uk-form-label">上傳物品其他圖片</h3>
                    <div class="uk-placeholder uk-text-center">
                        <span uk-icon="icon: cloud-upload"></span>
                        <span class="uk-text-middle">拖曳圖片到此處，或</span>
                        <div uk-form-custom>
                            <input type="file" multiple id="gallery">
                            <span class="uk-link">選擇圖片</span>
                        </div>
                    </div>

                    <div class="uk-text-meta">支援的圖片格式：jpg、png</div>
                    <ul id="sortable-list" class="uk-grid-small uk-child-width-1-4@s uk-flex-center uk-flex-middle uk-grid" uk-grid uk-sortable="handle: .uk-card">
                        <!-- 這裡的 li 將由 jQuery 動態添加 -->
                    </ul>
                </div>
            </div>
            <a class="uk-button custom-button-1 uk-width-auto@s" id="submit-form">確認</a>

        </form>

    </div>
@endsection
@push('scripts')
    <script>

        $(function () {
            let selectedFiles = [];

            // When files are selected
            $('#gallery').on('change', function(event) {
                selectedFiles = Array.from(event.target.files); // Convert FileList to Array

                $.each(selectedFiles, function(index, file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const li = $('<li>').append(
                            $('<div>').addClass('uk-card uk-card-default uk-card-body uk-card-small uk-text-center').append(
                                $('<img>').attr('src', e.target.result).css('max-width', '100%')
                            )
                        ).attr('data-index', index); // Store the file index in the list item
                        $('#sortable-list').append(li);
                    };

                    reader.readAsDataURL(file);
                });
            });

            // When the form is submitted
            $('#submit-form').click(function (event) {
                event.preventDefault(); // Prevent the form from submitting the traditional way

                let inputData = new FormData($('#lot-form')[0]);

                // Get the order of the list items
                $('#sortable-list li').each(function() {
                    let index = $(this).attr('data-index');
                    inputData.append('images[]', selectedFiles[index]); // Append files in the correct order
                });

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "post",
                    url: '{{ route('test.post') }}',
                    data: inputData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        console.log('Files successfully uploaded:', response);
                    },
                    error: function (error) {
                        console.error('An error occurred:', error);
                    }
                });
            });
        });

    </script>


@endpush

