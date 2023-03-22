<?php

namespace App\Presenters;

class ExpertLotIndexPresenter
{
    public function present($lot, $mainCategory)
    {
        switch (true) {
            case $lot->status == 0:
                return '<a href="'.route('expert.lots.review', ['mainCategoryId'=>$mainCategory->id, 'lotId'=>$lot->id]).'" class="uk-button custom-button-1">審核</a>';
            case $lot->status == 3:
               return '
                        <div class="uk-text-left">
                                <div id="receive-lot-'.$lot->id.'" class="modal">
                                    <h2>確定將收到物品 '.$lot->id.' 嗎？</h2>
                                    <form method="post" action="'.route('expert.lots.receive', [$mainCategory->id, $lot->id]).'">
                                        <input type="hidden" name="_token" value="'.csrf_token().'" />
                                        <input type="text" name="lotId" value="'.$lot->id.'" hidden>
                                        <p class="uk-text-right">
                                            <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                            <button class="uk-button custom-button-1 take-down" type="submit" lotId="'.$lot->id.'">確定</button>
                                        </p>
                                    </form>
                                </div>
                                <a href="#receive-lot-'.$lot->id.'" rel="modal:open" class="uk-button custom-button-1">收到物品</a>
                            </div>
                    ';
            case $lot->status >=10 && $lot->status <20:
                return '
                            <div class="uk-text-left">
                                <div id="take-down-lot-'.$lot->id.'" class="modal">
                                    <h2>確定將 物品'.$lot->id.' 下架嗎？</h2>
                                    <form method="post" action="'.route('expert.lots.take-down', [$mainCategory->id, $lot->id]).'">
                                        <input type="hidden" name="_token" value="'.csrf_token().'" />
                                        <input type="text" name="lotId" value="'.$lot->id.'" hidden>
                                        <p class="uk-text-right">
                                            <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                            <button class="uk-button custom-button-1 take-down" type="submit" lotId="'.$lot->id.'">確定</button>
                                        </p>
                                    </form>
                                </div>
                                <a href="#take-down-lot-'.$lot->id.'" rel="modal:open" class="uk-button custom-button-2">下架</a>
                            </div>
                        ';
            case $lot->status == 30 || $lot->status == 35:
                return '
                        <a href="'.route('expert.unsold_lot_logistic_info.create', [$mainCategory->id, $lot->id]).'" class="uk-button custom-button-1">查看 / 填寫 退還資訊</a>
                    ';
            case $lot->status == 33:
                return '
                        <a href="'.route('expert.returned_lot_logistic_info.edit', [$mainCategory->id, $lot->id]).'" class="uk-button custom-button-1">查看 / 填寫 退還資訊</a>
                    ';
            default :
                return '

                ';
        }
    }


}
