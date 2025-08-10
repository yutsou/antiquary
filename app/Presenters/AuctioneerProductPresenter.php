<?php

namespace App\Presenters;

class AuctioneerProductPresenter
{
    public function present($lot)
    {
        switch (true) {
            case $lot->status == 60:
                return '
                    <div class="uk-text-right">
                        <div id="receive-lot-' . $lot->id . '" class="modal">
                            <h2>確定上架物品 #' . $lot->id . ' 嗎？</h2>
                            <form method="post" action="' . route('auctioneer.products.publish', [$lot->id]) . '">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <input type="hidden" name="lotId" value="' . $lot->id . '">
                                <p class="uk-text-right">
                                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                    <button type="submit" class="uk-button custom-button-1 take-down" lotId="' . $lot->id . '">確定</button>
                                </p>
                            </form>
                        </div>
                        <a href="#receive-lot-' . $lot->id . '" rel="modal:open" class="uk-button custom-button-1">上架物品</a>
                    </div>
                ';
            case $lot->status == 61:
                return '
                    <div class="uk-text-right">
                        <div id="receive-lot-' . $lot->id . '" class="modal">
                            <h2>確定下架物品 #' . $lot->id . ' 嗎？</h2>
                            <form method="post" action="' . route('auctioneer.products.unpublish', [$lot->id]) . '">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <input type="hidden" name="lotId" value="' . $lot->id . '">
                                <p class="uk-text-right">
                                    <a href="#" rel="modal:close" class="uk-button uk-button-default">取消</a>
                                    <button type="submit" class="uk-button custom-button-1 take-down" lotId="' . $lot->id . '">確定</button>
                                </p>
                            </form>
                        </div>
                        <a href="#receive-lot-' . $lot->id . '" rel="modal:open" class="uk-button custom-button-3">下架物品</a>
                    </div>
                ';
            default:
                return '';
        }
    }
}
