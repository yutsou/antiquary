<?php

namespace App\Presenters;

use App\Models\Domain;

class ExpertDashboardPresenter
{
    public function getLotManagementCount(Domain $domain)
    {
        $count =  $domain->lot_management_count;
        if($count != 0) {
            return '<span class="uk-badge" style="background-color: #d62828;">'.$count.'</span>';
        } else {
            return '';
        }
    }

    public function getAuctionManagementCount(Domain $domain)
    {
        $count =  $domain->auction_management_count;
        if($count != 0) {
            return '<span class="uk-badge" style="background-color: #d62828;">'.$count.'</span>';
        } else {
            return '';
        }
    }

}
