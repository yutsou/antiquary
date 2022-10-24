<?php

namespace App\Repositories;

use App\Models\Specification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SpecificationRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Specification $specification)
    {
        $this->model = $specification;
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
        if (null == $specification = $this->model->find($id)) {
            throw new ModelNotFoundException("Specification not found");
        }

        return $specification;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }
}
