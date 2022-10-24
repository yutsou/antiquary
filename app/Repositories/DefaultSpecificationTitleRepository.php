<?php

namespace App\Repositories;

use App\Models\DefaultSpecificationTitle;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DefaultSpecificationTitleRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(DefaultSpecificationTitle $defaultSpecificationTitle)
    {
        $this->model = $defaultSpecificationTitle;
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
        if (null == $defaultSpecificationTitle = $this->model->find($id)) {
            throw new ModelNotFoundException("DefaultSpecificationTitle not found");
        }

        return $defaultSpecificationTitle;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }

    public function deleteByCategoryId($categoryId)
    {
        return $this->model->where('category_id', $categoryId)->delete();
    }
}
