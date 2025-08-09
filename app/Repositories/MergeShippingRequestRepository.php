<?php

namespace App\Repositories;

use App\Models\MergeShippingRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MergeShippingRequestRepository
{
    protected $model;

    public function __construct(MergeShippingRequest $mergeShippingRequest)
    {
        $this->model = $mergeShippingRequest;
    }

    public function find($id)
    {
        if (null == $mergeShippingRequest = $this->model->find($id)) {
            throw new ModelNotFoundException("MergeShippingRequest not found");
        }

        return $mergeShippingRequest;
    }

    public function createLogisticRecord(array $data, $id)
    {
        $this->model->find($id)->logisticRecords()->create($data);
    }

    public function updateLogisticRecord(array $data, $id, $type)
    {
        $model = $this->model->find($id);
        $model->logisticRecords()->where('type', $type)->update($data);
        return $model;
    }
}
