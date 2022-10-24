<?php

namespace App\Presenters;

class LotEditPresenter
{
    public function displayReservePrice($lot)
    {
        if($lot->reserve_price === null) {
            return '
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">底價</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="check-reserve-price">
                                <input type="checkbox" id="check-reserve-price" name="checkReversePrice">
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">設置底價（需高於NT$3000）</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" id="reserve-price" name="reserve_price" type="number" disabled>
                        </div>
                    </div>
                </div>
            </div>
            ';
        } else {
            return '
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">底價</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="check-reserve-price">
                                <input type="checkbox" id="check-reserve-price" name="checkReversePrice" checked>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">設置底價（需高於NT$3000）</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" id="reserve-price" name="reserve_price" type="number" value="'.intval($lot->reserve_price).'">
                        </div>
                    </div>
                </div>
            </div>
            ';
        }
    }

    public function displayEntrust($lot) {
        if($lot->owner->role === 2) {
            if($lot->entrust === 0) {
                return '
                    <div class="uk-margin">
                        <div class="uk-card uk-card-default uk-card-body">
                            <div class="uk-grid uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    <h3 class="uk-card-title uk-form-label">是否寄給拍賣會委賣</h3>
                                </div>
                                <div class="uk-width-auto" style="padding-top: 5px">
                                    <label class="uk-switch" for="check-entrust">
                                        <input type="checkbox" id="check-entrust" name="entrust">
                                        <div class="uk-switch-slider"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                ';
            } else {
                return '
                    <div class="uk-margin">
                        <div class="uk-card uk-card-default uk-card-body">
                            <div class="uk-grid uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    <h3 class="uk-card-title uk-form-label">是否寄給拍賣會委賣</h3>
                                </div>
                                <div class="uk-width-auto" style="padding-top: 5px">
                                    <label class="uk-switch" for="check-entrust">
                                        <input type="checkbox" id="check-entrust" name="entrust" checked>
                                        <div class="uk-switch-slider"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                ';
            }
        }
    }

    public function displayFaceToFace($lot){
        if($lot->face_to_face === null){
            return '
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">允許面交</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="face-to-face">
                                <input type="checkbox" id="face-to-face" name="faceToFace">
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            ';
        } else {
            return '
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">允許面交</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="face-to-face">
                                <input type="checkbox" id="face-to-face" name="faceToFace" checked>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            ';
        }
    }

    public function displayHomeDelivery($lot) {
        if($lot->home_delivery === null) {
            return '
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">允許宅配</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="home-delivery">
                                <input type="checkbox" id="home-delivery" name="homeDelivery">
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">預估買家負擔宅配金額 (設置為0的話將由委託者負擔運費)</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" type="number" id="home-delivery-cost" name="homeDeliveryCost" disabled>
                        </div>
                    </div>
                </div>
            </div>
            ';
        } else {
            return '
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">允許宅配</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="home-delivery">
                                <input type="checkbox" id="home-delivery" name="homeDelivery" checked>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">預估買家負擔宅配金額 (設置為0的話將由委託者負擔運費)</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" type="number" id="home-delivery-cost" name="homeDeliveryCost" value="'.intval($lot->home_delivery->cost).'">
                        </div>
                    </div>
                </div>
            </div>
            ';
        }
    }

    public function displayCrossBorderDelivery($lot) {
        if($lot->cross_border_delivery === null) {
            return '
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">允許境外宅配</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="cross-border-delivery">
                                <input type="checkbox" id="cross-border-delivery" name="crossBorderDelivery">
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">預估買家負擔宅配金額 (設置為0的話將由委託者負擔運費)</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" type="number" id="cross-board-delivery-cost"
                                   name="crossBorderDeliveryCost" disabled>
                        </div>
                    </div>
                </div>
            </div>
            ';
        } else{
            return '
            <div class="uk-margin">
                <div class="uk-card uk-card-default uk-card-body">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <h3 class="uk-card-title uk-form-label">允許境外宅配</h3>
                        </div>
                        <div class="uk-width-auto" style="padding-top: 5px">
                            <label class="uk-switch" for="cross-border-delivery">
                                <input type="checkbox" id="cross-border-delivery" name="crossBorderDelivery" checked>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-text-default">預估買家負擔宅配金額 (設置為0的話將由委託者負擔運費)</label>
                        <div class="uk-inline" style="display: block;">
                            <span class="uk-form-icon" uk-icon="icon: tag"></span>
                            <input class="uk-input" type="number" id="cross-board-delivery-cost"
                                   name="crossBorderDeliveryCost" value="'.intval($lot->cross_border_delivery->cost).'">
                        </div>
                    </div>
                </div>
            </div>
            ';
        }
    }
}
