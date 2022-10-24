<?php

namespace App\Services;

class SmsService
{
    function smsSend($mobile,$message){
        $longsms="Y";
        $message = urlencode($message);

        $data = array(
            "username" => 'evilfish0305',
            "password" => 'eagle111',
            "mobile" => $mobile,
            "longsms" => $longsms,
            "message" => $message
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.twsms.com/json/sms_send.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $HTTPResponse = curl_exec($ch);
        $resultArray = curl_getinfo($ch);
        $http_header_code = $resultArray["http_code"];
        curl_close ($ch);

        if ($http_header_code=="200"){
            $status = json_decode($HTTPResponse);
        }

        return $status;
    }

    function smsQuery($username,$password,$mobile,$msgid){
        $data = array(
            "username" => $username,
            "password" => $password,
            "mobile" => $mobile,
            "msgid" => $msgid
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.twsms.com/json/sms_query.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $HTTPResponse = curl_exec($ch);
        $resultArray = curl_getinfo($ch);
        $http_header_code = $resultArray["http_code"];
        curl_close ($ch);

        if ($http_header_code=="200"){
            $status = json_decode($HTTPResponse);
        }

        return $status;
    }
}
