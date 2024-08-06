$(function () {
    $( ".complete-order" ).click(function() {
        let orderId = $(this).attr('orderId');
        let actionUrl = $(this).attr('actionUrl');
        let redirectUrl = $(this).attr('redirectUrl');
        //let url = '{{ route("account.orders.complete", ":id") }}';
        //url = url.replace(':id', orderId);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "post",
            url: actionUrl,
            success: function () {
                window.location.assign(redirectUrl);
            }
        });
    });
});

$(document).on('click', '.notice-remit', function(){
    let orderId = $(this).attr('orderId');
    let actionUrl = $(this).attr('actionUrl');
    let redirectUrl = $(this).attr('redirectUrl');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "post",
        url: actionUrl,
        success: function () {
            window.location.assign(redirectUrl);
        }
    });
});

$(document).on('click', '.notice-shipping', function(){
    let orderId = $(this).attr('orderId');
    let actionUrl = $(this).attr('actionUrl');
    let redirectUrl = $(this).attr('redirectUrl');
    let logisticsName = $('#logistics-name-'+orderId).val();
    let trackingCode = $('#tracking-code-'+orderId).val();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "post",
        url: actionUrl,
        data: { logistics_name:logisticsName, tracking_code:trackingCode },
        success: function () {
            window.location.assign(redirectUrl);
        }
    });
});

$(document).on('click', '.notice-arrival', function(){
    let orderId = $(this).attr('orderId');
    let actionUrl = $(this).attr('actionUrl');
    let redirectUrl = $(this).attr('redirectUrl');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "post",
        url: actionUrl,
        success: function () {
            window.location.assign(redirectUrl);
        }
    });
});

$(document).on('click', '.notice-confirm-atm-pay', function(){
    let orderId = $(this).attr('orderId');
    let actionUrl = $(this).attr('actionUrl');
    let redirectUrl = $(this).attr('redirectUrl');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "post",
        url: actionUrl,
        success: function () {
            window.location.assign(redirectUrl);
        }
    });
});

$(document).on('click', '.confirm-refill-transfer-info', function(){
    let orderId = $(this).attr('orderId');
    let actionUrl = $(this).attr('actionUrl');
    let redirectUrl = $(this).attr('redirectUrl');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "post",
        url: actionUrl,
        success: function () {
            window.location.assign(redirectUrl);
        }
    });
});
$(document).on('click', '.set-withdrawal-bid', function(){
    let orderId = $(this).attr('orderId');
    let actionUrl = $(this).attr('actionUrl');
    let redirectUrl = $(this).attr('redirectUrl');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "post",
        url: actionUrl,
        success: function () {
            window.location.assign(redirectUrl);
        }
    });
});

$(document).on('click', '.confirm-paid', function(){
    let orderId = $(this).attr('orderId');
    let actionUrl = $(this).attr('actionUrl');
    let redirectUrl = $(this).attr('redirectUrl');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "post",
        url: actionUrl,
        success: function () {
            window.location.assign(redirectUrl);
        }
    });
});
