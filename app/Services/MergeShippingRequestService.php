<?php

namespace App\Services;

use App\Models\MergeShippingRequest;
use App\Repositories\MergeShippingRequestRepository;

class MergeShippingRequestService extends MergeShippingRequestRepository
{
    public function getMergeShippingRequestCount()
    {
        return MergeShippingRequest::where('status', MergeShippingRequest::STATUS_PENDING)
            ->count();
    }
}
