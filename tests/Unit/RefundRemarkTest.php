<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\OrderRecord;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Repositories\OrderRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RefundRemarkTest extends TestCase
{
    use DatabaseTransactions;

    public function test_refund_with_custom_remark()
    {
        // 創建測試用戶
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'phone' => '0912345678'
        ]);

        // 創建測試訂單
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 60, // 退款狀態
            'payment_method' => 1,
            'delivery_method' => 1,
            'payment_due_at' => now()->addDays(7),
            'subtotal' => 1000,
            'premium' => 100,
            'total' => 1100
        ]);

        // 測試使用 Repository 創建帶備注的退款記錄
        $orderRepository = new OrderRepository($order);
        $orderRecord = $orderRepository->createOrderRecord(61, $order->id, '客戶要求退款，商品有瑕疵');

        // 驗證備注已保存
        $this->assertEquals('客戶要求退款，商品有瑕疵', $orderRecord->remark);

        // 驗證可以從資料庫中檢索
        $retrievedRecord = OrderRecord::find($orderRecord->id);
        $this->assertEquals('客戶要求退款，商品有瑕疵', $retrievedRecord->remark);
    }

    public function test_refund_remark_format()
    {
        // 創建測試用戶
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'phone' => '0912345679'
        ]);

        // 創建測試訂單
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 60,
            'payment_method' => 2, // LINE Pay
            'delivery_method' => 1,
            'payment_due_at' => now()->addDays(7),
            'subtotal' => 2000,
            'premium' => 200,
            'total' => 2200
        ]);

        // 模擬完整的退款備注格式
        $refundAmount = 2200;
        $refundMethod = 'line_pay';
        $customRemark = '客戶取消訂單';

        $remark = '退款金額：NT$' . number_format($refundAmount);
        if ($refundMethod == 'line_pay') {
            $remark .= '，退款方式：LINE Pay';
        } else {
            $remark .= '，退款方式：銀行轉帳';
        }

        if ($customRemark) {
            $remark .= '，備注：' . $customRemark;
        }

        // 創建訂單記錄
        $orderRepository = new OrderRepository($order);
        $orderRecord = $orderRepository->createOrderRecord(61, $order->id, $remark);

        // 驗證備注格式正確
        $expectedRemark = '退款金額：NT$2,200，退款方式：LINE Pay，備注：客戶取消訂單';
        $this->assertEquals($expectedRemark, $orderRecord->remark);
    }
}
