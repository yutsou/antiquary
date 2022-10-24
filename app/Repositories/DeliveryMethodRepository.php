<?php

namespace App\Repositories;

use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeliveryMethodRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(DeliveryMethod $deliveryMethod)
    {
        $this->model = $deliveryMethod;
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
        if (null == $deliveryMethod = $this->model->find($id)) {
            throw new ModelNotFoundException("DeliveryMethod not found");
        }

        return $deliveryMethod;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }
}
