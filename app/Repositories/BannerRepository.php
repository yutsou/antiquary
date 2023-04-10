<?php

namespace App\Repositories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BannerRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Banner $banner)
    {
        $this->model = $banner;
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
        if (null == $banner = $this->model->find($id)) {
            throw new ModelNotFoundException("Banner not found");
        }

        return $banner;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }
}
