<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
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
        if (null == $user = $this->model->find($id)) {
            throw new ModelNotFoundException("User not found");
        }

        return $user;
    }

    public function fill(array $data, $id)
    {
        $user = $this->model->find($id)->fill($data);
        if($user->isDirty('phone')) {
            $user->phone_verified_at = null;
        }
        if($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        return$user->save();
    }

    public function getLotNoticeCount(array $status, $id)
    {
        $user = $this->model->find($id);
        return $user->ownLots()->whereIn('status', $status)->count();
    }

    public function getOrderNoticeCount(array $status, $id)
    {
        $user = $this->model->find($id);
        return $user->orders()->whereIn('status', $status)->count();
    }
}
