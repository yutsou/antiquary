@extends('layouts.member')

@section('content')
    <FORM action="https://n.gomypay.asia/TestShuntClass.aspx" method="post">
        <input name='Send_Type' value='0'>
        <input name='Pay_Mode_No' value='2'>
        <input name='CustomerId' value='E8C95C73255ED798EC637705A80B35BC'>
        <input name='Order_No' value='{{ rand() }}'>
        <input name='CardNo' value='4907060600015101'>
        <input name='ExpireDate' value='2412'>
        <input name='CVV' value='615'>
        <input name='TransMode' value='1'>
        <input name='Amount' value='35'>
        <input name='Installment' value='0'>
        <input name='TransCode' value='00'>
        <input name='Buyer_Name' value='Yu Tsou'>
        <input name='Buyer_Telm' value='0912649739'>
        <input name='Buyer_Mail' value='evilfishcoco@gmail.com'>
        <input name='Buyer_Memo' value='商品資訊'>
        <input name="Callback_Url" value="{{ route('testCallback') }}">
        <input name="Return_url" value="{{ route('testReturn') }}">


        <input name="button1" type="submit" class="sub_buttom" id="button1" value="確定付款(請勿重複 點選)" >
    </form>
@endsection

