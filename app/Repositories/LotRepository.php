<?php

namespace App\Repositories;

use App\Models\Lot;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LotRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Lot $lot)
    {
        $this->model = $lot;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id)
    {
        return $this->model->where('id', $id)
            ->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function find($id)
    {
        if (null == $lot = $this->model->find($id)) {
            throw new ModelNotFoundException("Lot not found");
        }

        return $lot;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
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
