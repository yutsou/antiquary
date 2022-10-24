<?php

namespace App\Repositories;

use App\Models\Image;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImageRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Image $image)
    {
        $this->model = $image;
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
        if (null == $image = $this->model->find($id)) {
            throw new ModelNotFoundException("Image not found");
        }

        return $image;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }

    public function getId($imageable_id, $imageable_type)
    {
        if (optional($this->model->where([['imageable_id', '=', $imageable_id], ['imageable_type', '=', $imageable_type]])->first())->id == null) {
            return null;
        }
        return $this->model->where([['imageable_id', '=', $imageable_id], ['imageable_type', '=', $imageable_type]])->first()->id;
    }
}
