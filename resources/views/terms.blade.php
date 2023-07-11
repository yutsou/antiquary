@extends('layouts.member')

@section('content')
    <div class="uk-margin uk-text-small">
        <a href="/" class="custom-color-1 custom-link-mute">首頁</a> > <a href="{{ URL::current() }}" class="custom-color-1 custom-link-mute">{{ $head }}</a>
    </div>
    <div class="uk-margin-medium">
        <h1 class="uk-heading-medium">{{ $head }}</h1>
    </div>
    <div class="uk-margin">

        <ul class="uk-list uk-list-disc">
            <li>使用者資格：使用者必須年滿18歲並註冊與驗證合法身份，始得於Antiquary網站上進行競標或委託拍賣。</li>
            <li>使用者責任：委託拍賣使用者應負責提供準確且合法的拍品信息，並確保有權將拍品寄售於Antiquary網站上。使用者也應遵守所有適用的法律法規，並遵守Antiquary網站的規定。</li>
            <li>物品描述和真實性：委託拍賣使用者應提供準確的拍品描述，包括拍品狀態、大致年代、取得來源並提供拍品的尺寸、材質等信息，也應確保所提供的拍品圖片真實反映拍品的外觀及品項。</li>
            <li>委託拍賣契約：委託拍賣使用者上傳物品審核時，代表已同意與Antiquary網站簽訂委託拍賣契約，已明確清楚委託拍賣相關條款和細節。</li>
            <li>拍賣程序：由Antiquary網站安排拍賣流程，包括競拍物品排序、開始和結束時間、出價方式、競標者資格和限制等。</li>
            <li>交易完成和付款：當拍賣結束時，交易程序按照競標須知進行，包括付款方式、交付物品的責任和期限等。</li>
            <li>智慧財產權保護：Antiquary網站內容和商標的智慧財產權。使用者不得未經授權使用、複製或散布Antiquary網站內容或商標。</li>
            <li>責任限制：Antiquary網站不對委託拍賣使用者或競標使用者之間的交易進行任何擔保或保證。Antiquary網站提供平台服務不對任何因委託拍賣使用者或競標使用者的行為而引起的損失或損害負責。</li>
            <li>爭議解決：任何因使用者條款引起的爭議，雙方同意以台灣士林地方法院為專屬管轄法院。若雙方選擇以非訴訟的方式解決爭議，則爭議將提交至具有專業性、獨立性和公正性的仲裁機構，依據其相應的仲裁規則進行仲裁。仲裁的地點為台灣，仲裁語言為中文。仲裁裁決將具有最終和具約束力。</li>
            <li>使用者條款的修改和終止：Antiquary保留修改或終止使用者條款的權利。</li>
        </ul>

    </div>
@endsection

