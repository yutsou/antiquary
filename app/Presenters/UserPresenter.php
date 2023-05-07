<?php

namespace App\Presenters;

class UserPresenter
{
    public function presentStatus($status)
    {
        return match ($status) {
            0 => '良好',
            1 => '第一次封鎖',
            2 => '違規一次',
            3 => '違規兩次封鎖',
            4 => '管理員封鎖'
        };
    }

    public function presentRole($status)
    {
        return match ($status) {
            0 => '拍賣家',
            1 => '專家',
            2 => '高級會員',
            3 => '普通會員',
        };
    }
}
