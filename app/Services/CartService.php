<?php

namespace App\Services;

use App\Repositories\CartRepository;

class CartService extends CartRepository
{
     public function addToCart($userId, $lotId, $quantity = 1)
    {
        return $this->addOrUpdateCartItem($userId, $lotId, $quantity);
    }

    public function getCartItems($userId)
    {
        $cartItems = $this->getUserCart($userId);
        $lots = $cartItems->map(function ($cartItem) {
            // 你也可以把購買數量加進 lot 物件（作為動態屬性）
            $lot = $cartItem->lot;
            $lot->cart_quantity = $cartItem->quantity;
            // 競標商品使用 current_bid，一般商品使用 reserve_price
            $price = $lot->type === 0 ? $lot->current_bid : $lot->reserve_price;
            $lot->subtotal = $cartItem->quantity * $price;
            return $lot;
        });
        return $lots;
    }

    public function updateCartQuantity($userId, $lotId, $quantity)
    {
        return $this->updateCartItemQuantity($userId, $lotId, $quantity);
    }

    public function getSelectedCartItems($userId, $selectedLotIds)
    {
        $cartItems = $this->getUserCart($userId)->whereIn('lot_id', $selectedLotIds);
        $lots = $cartItems->map(function ($cartItem) {
            $lot = $cartItem->lot;
            $lot->cart_quantity = $cartItem->quantity;
            // 競標商品使用 current_bid，一般商品使用 reserve_price
            $price = $lot->type === 0 ? $lot->current_bid : $lot->reserve_price;
            $lot->subtotal = $cartItem->quantity * $price;
            return $lot;
        });
        return $lots;
    }

    public function removeSelectedItems($userId, $selectedLotIds, $skipAuctionItems = true)
    {
        $cartItems = $this->getUserCart($userId)->whereIn('lot_id', $selectedLotIds);

        if ($skipAuctionItems) {
            // 過濾掉競標商品（type = 0），只移除一般商品
            $normalItems = $cartItems->filter(function ($cartItem) {
                return $cartItem->lot->type !== 0;
            });
            $lotIdsToRemove = $normalItems->pluck('lot_id')->toArray();
        } else {
            // 移除所有選中的商品，包括競標商品
            $lotIdsToRemove = $cartItems->pluck('lot_id')->toArray();
        }

        if (!empty($lotIdsToRemove)) {
            return $this->removeSelectedCartItems($userId, $lotIdsToRemove);
        }

        return true;
    }

    public function removeCartItem($userId, $lotId)
    {
        // 檢查是否為競標商品
        $cartItem = $this->model->where('user_id', $userId)
            ->where('lot_id', $lotId)
            ->first();

        if ($cartItem && $cartItem->lot->type === 0) {
            throw new \Exception('競標商品無法從購物車移除');
        }

        return $this->model->where('user_id', $userId)
            ->where('lot_id', $lotId)
            ->delete();
    }

    public function getCartCount($userId)
    {
        return $this->getUserCart($userId)->count();
    }
}
