@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">
        <p>
            Antiquary 承諾以使用者隱私安全為優先。我們使用您的資訊為了提供您更好的服務與保障。
        </p>
        <div>
            <ul class="uk-list uk-list-disc">
                <li>收集的資訊是使用我們的服務所必需的，例如您在註冊服務時必須提供的資訊，例如姓名、地址、電子郵件地址、電話號碼等。綁定帳號時（請注意，在您使用我們的服務時，即使您未登入至該服務，我們仍然可能會辨識出您或您的裝置）。我們可能會使用裝置 ID、cookies 及其他訊號，包括從第三方取得的資訊，將帳號及/或裝置與您建立關聯。</li>
                <li>我們使用使用者資訊來處理付款、收款、物流、提供客戶支援等。</li>
                <li>我們可能會蒐集您提供給我們的資訊，例如：註冊帳號、綁定帳號、填寫表單、瀏覽網站、加入最愛、標題查詢、觀看紀錄、頁面瀏覽次數、搜尋查詢、進行交易等過程中提供的資訊。</li>
                <li>資訊分享：交易中我們需要分享您的個人資訊，例如買方、賣方、第三方支付處理服務提供商、物流公司等。</li>
                <li>保護兒童隱私權，我們的服務僅限提供18歲以上的法定成年人。</li>
                <li>隱私政策的修改：Antiquary保留修改隱私政策的權利，我們可能會不時更新本隱私權保護政策，請您定期查看。</li>
            </ul>
        </div>
    </div>
@endsection

