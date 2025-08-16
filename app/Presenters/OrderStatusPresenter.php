<?php

namespace App\Presenters;

use Illuminate\Support\Facades\Auth;

class OrderStatusPresenter
{
    protected function statusList ($type, $status)
    {
        $statusList = match ($type) {
            0 => ['等待確認訂單'],
            1 => ['等待付款', '等待確認匯款', '付款完成，等待面交', '付款完成，等待出貨', '付款失敗，等待重新付款'],
            2 => ['已出貨', '等待買家確認物品'],
            4 => ['訂單已完成', '已匯款給賣家'],
            5 => ['失效 - 未確認訂單', '失效 - 付款逾期', '爭議 - 等待確認匯款', '爭議 - 等待確認刷卡狀態'],
            6 => ['爭議 - 要求退款', '爭議 - 已完成退款']
        };
        return $statusList[$status];
    }

    protected function transfer($inputStatus)
    {
        $type = intval($inputStatus/10);
        $status = $inputStatus%10;
        return $this->statusList($type, $status);
    }

    public function present($order)
    {
        if(Auth::user()->id == $order->user_id && intval($order->status/10) == 4 ) {
            return '訂單已完成';
        } elseif ($order->status == 20) {#已出貨
            $logisticInfo = $order->logisticRecords->where('type',0)->first();
            return $this->transfer($order->status).' - '.$logisticInfo->company_name.': '.$logisticInfo->tracking_code;
        } else {
            return $this->transfer($order->status);
        }

    }

    public function auctioneerOrderShowRecordTable($order)
    {
        $html = '<table class="uk-table uk-table-divider"><tbody>';
        foreach($order->orderRecords as $orderRecord) {
            $statusHtml = '<tr><td class="uk-table-expand">';
            switch (true) {
                case ($orderRecord->status == 11 || $orderRecord->status == 52):
                    $statusHtml = $statusHtml . $this->transfer($orderRecord->status);
                    $statusHtml = $statusHtml . '<br>帳號後五碼：' . $orderRecord->transactionRecord->remitter_account . ' 匯款金額NT$' . number_format($orderRecord->transactionRecord->amount);
                    break;
                case ($orderRecord->status == 12 || $orderRecord->status == 13 || $orderRecord->status == 53):
                    $statusHtml = $statusHtml . $this->transfer($orderRecord->status);
                    if ($order->payment_method === 2) {
                        $statusHtml = $statusHtml . '<br>LINE Pay付款編號：' . $orderRecord->transactionRecord->merchant_trade_no;
                    }
                    break;
                case ($orderRecord->status == 41):
                    $statusHtml = $statusHtml . $this->transfer($orderRecord->status);
                    $statusHtml = $statusHtml . '<br>收款帳號：' . $orderRecord->transactionRecord->payee_account . '</br>匯款金額NT$' . number_format($orderRecord->transactionRecord->amount);
                    break;
                default:
                    $statusHtml = $statusHtml . $this->transfer($orderRecord->status);
                    break;
            }

            // 顯示備注
            if ($orderRecord->remark) {
                $statusHtml = $statusHtml . '<br><span class="uk-text-meta">備注：' . $orderRecord->remark . '</span>';
            }

            $statusHtml = $statusHtml.'</td>';
            $statusHtml = $statusHtml.'<td class="uk-table-expand uk-text-right">'.$orderRecord->created_at.'</td></tr>';
            $html = $html.$statusHtml;
        }
        $html = $html.'</tbody></table>';
        return $html;
    }
}
