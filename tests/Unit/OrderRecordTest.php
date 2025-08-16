<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\OrderRecord;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderRecordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_order_record_can_have_remark()
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
            'status' => 0,
            'payment_method' => 1,
            'delivery_method' => 1,
            'payment_due_at' => now()->addDays(7),
            'subtotal' => 1000,
            'premium' => 100,
            'total' => 1100
        ]);

        // 創建帶備注的訂單記錄
        $orderRecord = OrderRecord::create([
            'order_id' => $order->id,
            'status' => 10,
            'remark' => '測試備注'
        ]);

        // 驗證備注已保存
        $this->assertEquals('測試備注', $orderRecord->remark);

        // 驗證可以從資料庫中檢索
        $retrievedRecord = OrderRecord::find($orderRecord->id);
        $this->assertEquals('測試備注', $retrievedRecord->remark);
    }

    public function test_order_record_remark_can_be_null()
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
            'status' => 0,
            'payment_method' => 1,
            'delivery_method' => 1,
            'payment_due_at' => now()->addDays(7),
            'subtotal' => 1000,
            'premium' => 100,
            'total' => 1100
        ]);

        // 創建不帶備注的訂單記錄
        $orderRecord = OrderRecord::create([
            'order_id' => $order->id,
            'status' => 10,
            'remark' => null
        ]);

        // 驗證備注為 null
        $this->assertNull($orderRecord->remark);
    }
}
