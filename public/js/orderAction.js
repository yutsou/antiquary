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

$(document).on('click', '.request-refund', function(){
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

$(document).on('click', '.confirm-refund', function(){
    let orderId = $(this).attr('orderId');
    let actionUrl = $(this).attr('actionUrl');
    let redirectUrl = $(this).attr('redirectUrl');
    let refundAmount = $('input[name="refund_amount"]').val();
    let refundMethod = $('input[name="refund_method"]:checked').val();
    let refundRemark = $('textarea[name="refund_remark"]').val();

    if (!refundAmount || refundAmount <= 0) {
        alert('請輸入有效的退款金額');
        return;
    }

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "post",
        url: actionUrl,
        data: {
            refund_amount: refundAmount,
            refund_method: refundMethod,
            refund_remark: refundRemark
        },
        success: function () {
            window.location.assign(redirectUrl);
        },
        error: function(xhr, status, error) {
            alert('退款處理失敗，請稍後再試');
        }
    });
});
