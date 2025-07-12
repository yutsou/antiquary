<?php

namespace App\Presenters;

class LotStatusPresenter
{
    protected function transfer ($type, $status)
    {
        $statusList = match ($type) {
            0 => ['等待審核', '申請需要修改', '審核成功，尚未填寫物流碼', '審核成功，等待物品送達'],
            1 => ['審核成功 - 等待競標', '收貨成功 - 等待競標' , '流標 - 重新競標', '棄標 - 重新競標'],
            2 => ['已安排拍賣會', '拍賣進行中', '競標成功 - 等待買家完成付款', '競標成功 - 等待買家完成交易', '流標 - 無人競標', '流標 - 未達底價', '棄標'],
            3 => ['流標 - 物品退還', '流標 - 物品退還 - 已寄出', '下架 - 物品退還給賣家 - 等待填寫收件資訊', '下架 - 物品退還給賣家 - 等待退回', '下架 - 物品退還給賣家 - 已寄出', '棄標 - 物品退還', '棄標 - 物品退還 - 已寄出'],
            4 => ['完成委賣 - 等待匯款', '完成委賣 - 已匯款'],
            5 => ['爭議'],
            6 => [ // 新增的「賣場直售商品」流程
                '未上架',
                '上架中',
                '買家下單 - 等待付款',
                '買家已付款 - 等待出貨',
                '已出貨 - 運送中',
                '買家已收貨',
                '交易完成',
                '買家申請退貨',
                '退貨完成',
                '交易取消'
            ]
        };
        return $statusList[$status];
    }

    public function present(int $inputStatus)
    {
        $type = intval($inputStatus/10);
        $status = $inputStatus%10;
        return $this->transfer($type, $status);
    }
}
