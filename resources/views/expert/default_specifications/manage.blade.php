@extends('layouts.expert')

@section('content')
<div class="uk-margin-medium">
    <h1 class="uk-heading-medium">{{ $head }}</h1>
</div>
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
<script>
    $(function () {
        $("#addSpecification").click(function(){
            let newSpecificationCount = $(".specifications").length;
            let newSpecification = '<div class="uk-margin"><input type="text" class="uk-input uk-form-width-small specifications" id="specification'+String(parseInt(newSpecificationCount))+'" name="titles[]" required></div>';
            $("#specifications").append(newSpecification);

        });
        $("#removeSpecification").click(function(){
            let specificationsAmount = $(".specifications").length;
            if(specificationsAmount !== 1) {
                $("#specification"+String(parseInt(specificationsAmount)-1)).remove();
                specificationsAmount --;
            }
        });
    });
</script>
<form class="uk-form-stacked" method="POST" action="{{ route('expert.default_specification_titles.store', ['mainCategoryId'=>$mainCategoryId]) }}" enctype="multipart/form-data">
    @csrf
    <div>
        <label class="uk-margin-right">預設規格名稱</label>
        <a class="uk-icon-button custom-color-group-1 uk-margin-small-right" id="addSpecification" uk-icon="icon: plus"></a><a class="uk-icon-button custom-color-group-1" id="removeSpecification" uk-icon="icon: minus"></a>
    </div>

        <div id="specifications" class="uk-margin">
            @foreach($defaultSpecificationTitles as $i=>$defaultSpecificationTitle)
                <div class="uk-margin">
                    <input type="text" class="uk-input uk-form-width-small specifications" id="specification{{$i}}" name="titles[]" value="{{ $defaultSpecificationTitle->title }}" required>
                </div>
            @endforeach
        </div>

    <div class="uk-margin">
        <button class="uk-button custom-button-1">儲存</button>
    </div>
</form>
@endsection
