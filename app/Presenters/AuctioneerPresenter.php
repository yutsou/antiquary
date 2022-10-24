<?php

namespace App\Presenters;

class AuctioneerPresenter
{
    public function checked($expert, $mainCategory)
    {
        $expertDomains = $expert->domains->pluck('domain')->toArray();

        $mainCategoryId = $mainCategory->id;

        if(in_array($mainCategoryId, $expertDomains)) {
            return '<label><input class="uk-checkbox" type="checkbox" name="domains[]" value="'.$mainCategory->id.'" checked>'.$mainCategory->name.'</label>';
        } else {
            return '<label><input class="uk-checkbox" type="checkbox" name="domains[]" value="'.$mainCategory->id.'">'.$mainCategory->name.'</label>';
        }
    }
}
