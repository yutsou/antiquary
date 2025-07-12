<?php

namespace App\Repositories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Cart $cart)
    {
        $this->model = $cart;
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
        return $this->model->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function find($id)
    {
        if (null == $cart = $this->model->find($id)) {
            throw new ModelNotFoundException("Cart not found");
        }
        return $cart;
    }

    public function fill(array $data, $id)
    {
        return $this->model->find($id)->fill($data)->save();
    }

    // 取得某會員的購物車（可依需求修改，這裡假設每人只會有一台 active 購物車）
    public function getUserCart($userId)
    {
        return $this->model->where('user_id', $userId)->get();
    }

    // 新增或更新某商品到購物車（自訂，可根據 lot_id、user_id 判斷是否已有、數量增減）
    public function addOrUpdateCartItem($userId, $lotId, $quantity = 1)
    {
        $cartItem = $this->model->where('user_id', $userId)
            ->where('lot_id', $lotId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
            return $cartItem;
        } else {
            return $this->model->create([
                'user_id' => $userId,
                'lot_id' => $lotId,
                'quantity' => $quantity,
            ]);
        }
    }

    // 移除購物車內某一商品
    public function removeCartItem($userId, $lotId)
    {
        return $this->model->where('user_id', $userId)
            ->where('lot_id', $lotId)
            ->delete();
    }

    // 更新購物車內某一商品的數量
    public function updateCartItemQuantity($userId, $lotId, $quantity)
    {
        $cartItem = $this->model->where('user_id', $userId)
            ->where('lot_id', $lotId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity = $quantity;
            $cartItem->save();
            return $cartItem;
        }

        return null;
    }

    // 移除選中的購物車商品
    public function removeSelectedCartItems($userId, $selectedLotIds)
    {
        return $this->model->where('user_id', $userId)
            ->whereIn('lot_id', $selectedLotIds)
            ->delete();
    }
}
