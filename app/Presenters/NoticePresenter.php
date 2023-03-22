<?php

namespace App\Presenters;

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
        switch ($notice->type) {
            case 0:
                return match ($notice->code) {
                    0 => ['您有新的訊息', '訂單 No.'.$notice->target_id.' ，有新的訊息。'],
                };
            case 1:
                return match ($notice->code) {
                    0 => ['正在審核', '我們剛剛收到您委賣的申請，您的物品編號是 No.'.$notice->target_id.' ，有新的變動時我們將會通知您。'],
                    1 => ['審核通過', '物品編號 No.'.$notice->target_id.' 審核通過，等待填寫物流碼。'],
                    2 => ['審核通過', '物品編號 No.'.$notice->target_id.' 審核通過，等待競標。'],
                    3 => ['申請需要修改', '物品編號 No.'.$notice->target_id.' 申請需要修改，等待修改物品資料。'],
                    4 => ['收到物品', '物品編號 No.'.$notice->target_id.' 收到物品，等待競標。'],
                };
            case 2:
                return match ($notice->code) {
                    0 => ['已安排競標', '物品 No.'.$notice->target_id.' ，安排於'.$notice->lot->auction_start_at_format.'開始競標。'],
                    1 => ['競標成功', '物品 No.'.$notice->target_id.' ，以 NT$'.number_format($notice->lot->current_bid).'賣出'],
                    2 => ['流標', '物品 No.'.$notice->target_id.' ，為達底價流標，請至平台選擇處理方式。'],
                    3 => ['流標', '物品 No.'.$notice->target_id.' ，無人競標流標，請至平台選擇處理方式。'],
                    4 => ['棄標', '物品 No.'.$notice->target_id.' ，遭到棄標，請至平台選擇處理方式。'],
                };
            case 3:
                return match ($notice->code) {
                    0 => ['已得標', '物品 No.'.$notice->target_id.' ，以 NT$'.number_format($notice->lot->current_bid).'得標'],
                    1 => ['已收到匯款', '訂單 No.'.$notice->target_id.' ，已收到匯款'],
                    2 => ['訂單已完成', '訂單 No.'.$notice->target_id.' ，已完成'],
                    3 => ['已完成委賣', '訂單 No.'.$notice->target_id.' ，已匯款'],
                };
            case 4:
                $logisticInfo = $notice->lot->logisticRecords->where('type',1)->first();
                return match ($notice->code) {
                    0 => ['下架的物品已寄出', '物品編號 No.'.$notice->target_id.' ，已寄出。'.$logisticInfo->company_name.': '.$logisticInfo->tracking_code],
                };
            case 5:
                $lot = $notice->lot;
                if( $lot->status == 30) {
                    $type = 2;
                } else { #35
                    $type = 3;
                }
                $logisticInfo = $lot->logisticRecords->where('type',$type)->first();
                return match ($notice->code) {
                    0 => ['流標的物品已寄出', '物品編號 No.'.$notice->target_id.' ，已寄出。'.$logisticInfo->company_name.': '.$logisticInfo->tracking_code],
                };
            case 6:
                return match ($notice->code) {
                    0 => ['付款提醒通知', '提醒您，訂單 No.' . $notice->target_id . ' 還未付款，請您在今天起的四天內完成付款，若逾時付款導致棄標，將被暫時停權一週，若為第二次棄標，您的帳號將會被永久停權。'],
                    1 => ['付款逾時', '訂單 No.' . $notice->target_id . ' 逾期付款，此次棄標將導致您停權一週，若棄標第二次，我們將永久停權您的帳號。'],
                    2 => ['付款逾時', '訂單 No.' . $notice->target_id . ' 逾期付款，此次棄標將導致您的帳號永久停權。'],
                };
        }
    }
}
