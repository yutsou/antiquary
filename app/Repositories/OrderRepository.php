<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Order $order)
    {
        $this->model = $order;
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
        if (null == $order = $this->model->find($id)) {
            throw new ModelNotFoundException("Order not found");
        }

        return $order;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }

    public function updateOrderStatus(int $status, $id, $remark = null){
        $model = $this->model->find($id);
        $model->update(['status' => $status]);
        $model->orderRecords()->create(['status'=>$status, 'remark'=>$remark]);
        return $model;
    }


    public function updateOrderStatusWithTransaction(array $data, int $status, $id, $remark = null){
        $model = $this->model->find($id);
        $model->update(['status' => $status]);
        $orderRecord = $model->orderRecords()->create(['status'=>$status, 'remark'=>$remark]);
        $data = array_merge($data, ['status' => $status]);
        $orderRecord->transactionRecord()->create($data);
    }

    public function createOrderRecord(int $status, $id, $remark = null)
    {
        return $this->model->find($id)->orderRecords()->create(['status'=>$status, 'remark'=>$remark]);
    }

    public function createLogisticRecord(array $data, $id)
    {
        $this->model->find($id)->logisticRecords()->create($data);
    }

    public function createTransactionRecord(array $data, $id)
    {
        return $this->model->find($id)->transactionRecords()->create($data);
    }
}
