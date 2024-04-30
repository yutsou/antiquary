<?php

namespace App\Presenters;

use App\Models\Lot;
use App\Models\Message;
use App\Models\Order;
use Carbon\Carbon;
use Exception;

class NoticePresenter
{
    public function transferNotice($notice)
    {
        $transferedNotice = $this->transferTemplateNotice($notice);
        return '
                    <div class="uk-card uk-card-default uk-card-small uk-card-body">
                        <div class="uk-child-width-expand" uk-grid>
                            <div><h3 class="uk-card-title">'.$transferedNotice[0].'</h3></div>
                            <div><p class="uk-text-right">'. $notice->created_at_format .'</p></div>
                        </div>

                        <p>
                            '.$transferedNotice[1].'
                        </p>
                    </div>
                ';
    }

    public function transferTemplateNotice($notice)
    {
        try {
            switch ($notice->type) {
                case 0:
                    $model = Message::find($notice->target_id);
                    return match ($notice->code) {
                        0 => ['您有新的訊息', '訂單 No.'.$model->order_id.' ，有新的訊息。'],
                    };
                case 1:
                    $model = Lot::find($notice->target_id);
                    return match ($notice->code) {
                        0 => ['正在審核', '我們剛剛收到您委賣的申請，您的物品編號是 No.'.$model->id.' ，有新的變動時我們將會通知您。'],
                        1 => ['審核通過', '物品編號 No.'.$model->id.' 審核通過，等待填寫物流碼。'],
                        2 => ['審核通過', '物品編號 No.'.$model->id.' 審核通過，等待競標。'],
                        3 => ['申請需要修改', '物品編號 No.'.$model->id.' 申請需要修改，等待修改物品資料。'],
                        4 => ['收到物品', '物品編號 No.'.$model->id.' 收到物品，等待競標。'],
                    };
                case 2:
                    $model = Lot::find($notice->target_id);
                    return match ($notice->code) {
                        0 => ['已安排競標', '物品 No.'.$model->id.' ，安排於 '.Carbon::createFromFormat('Y-m-d H:i:s', new Carbon($notice->content))->format('Y-m-d H:i').' 開始競標。'],
                        1 => ['流標', '物品 No.'.$model->id.' ，無人競標流標，請至平台選擇處理方式。'],
                        2 => ['流標', '物品 No.'.$model->id.' ，未達底價流標，請至平台選擇處理方式。'],
                        3 => ['棄標', '物品 No.'.$model->id.' ，遭到棄標，請至平台選擇處理方式。'],
                    };
                case 3:
                    $model = Order::find($notice->target_id);
                    return match ($notice->code) {
                        0 => ['已得標', '物品 No.'.$model->lot->id.' ，以 NT$'.number_format($model->subtotal).'得標，點選此"<a href="'.route('account.orders.show', $model).'">連結</a>"到付款頁面進行付款。'],
                        1 => ['競標成功', '物品 No.'.$model->lot->id.' ，以 NT$'.number_format($model->subtotal).'賣出。'],
                        2 => ['已收到匯款', '訂單 No.'.$model->id.' ，已收到匯款'],
                        3 => ['訂單已完成', '訂單 No.'.$model->id.' ，已完成'],
                        4 => ['已完成委賣', '訂單 No.'.$model->id.' ，已匯款'],
                    };
                case 4:
                    $model = Lot::find($notice->target_id);
                    $logisticInfo = $notice->lot->logisticRecords->where('type',1)->first();
                    return match ($notice->code) {
                        0 => ['下架的物品已寄出', '物品編號 No.'.$model->id.' ，已寄出。'.$logisticInfo->company_name.': '.$logisticInfo->tracking_code],
                    };
                case 5:
                    $lot = $notice->lot;
                    switch ($notice->code) {
                        case 0: //無人競標退回
                            $type = 2;
                            $text = '流標的物品已寄出';
                            break;
                        case 1: //放棄付款退回
                            $type = 3;
                            $text = '棄標的物品已寄出';
                            break;
                    }

                    $logisticInfo = $lot->logisticRecords->where('type',$type)->first();
                    return [$text, '物品編號 No.'.$lot->id.' ，已寄出。'.$logisticInfo->company_name.': '.$logisticInfo->tracking_code];
                case 6:
                    $model = Order::find($notice->target_id);
                    return match ($notice->code) {
                        0 => ['付款提醒通知', '提醒您，訂單 No.' . $model->id . ' 還未付款，請您在今天起的四天內完成付款，若逾時付款導致棄標，將被暫時停權一週，若為第二次棄標，您的帳號將會被永久停權。'],
                        1 => ['付款逾時', '訂單 No.' . $model->id . ' 逾期付款，此次棄標將導致您停權一週，若棄標第二次，我們將永久停權您的帳號。'],
                        2 => ['付款逾時', '訂單 No.' . $model->id . ' 逾期付款，此次棄標將導致您的帳號永久停權。'],
                    };
            }
        } catch (Exception $e) {
            $model = Message::find($notice->target_id);
            dd($e, $notice);
        }

    }
}
