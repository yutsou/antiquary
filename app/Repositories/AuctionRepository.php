<?php

namespace App\Repositories;

use App\Models\Auction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuctionRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Auction $auction)
    {
        $this->model = $auction;
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
        if (null == $auction = $this->model->find($id)) {
            throw new ModelNotFoundException("Auction not found");
        }

        return $auction;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }
}
