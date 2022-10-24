<?php

namespace App\Repositories;

use App\Models\Domain;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DomainRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Domain $domain)
    {
        $this->model = $domain;
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
        if (null == $domain = $this->model->find($id)) {
            throw new ModelNotFoundException("domain not found");
        }

        return $domain;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }

    public function getDomainByUserIdAndDomainId($userId, $domain)
    {
        return $this->model->where('user_id', $userId)->where('domain', $domain)->first();
    }

}
