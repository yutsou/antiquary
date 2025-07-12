<?php

namespace Tests\Unit;

use App\Models\Lot;
use App\Models\User;
use App\Services\LotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotServiceInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected $lotService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lotService = app(LotService::class);

        // 創建測試用戶
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_check_inventory_for_direct_sale_product()
    {
        // 創建一個直賣商品（status >= 60）
        $lot = Lot::factory()->create([
            'status' => 61, // 上架中
            'inventory' => 5,
            'owner_id' => $this->user->id,
        ]);

        // 測試庫存足夠的情況
        $this->assertTrue($this->lotService->checkInventory($lot->id, 3));

        // 測試庫存不足的情況
        $this->assertFalse($this->lotService->checkInventory($lot->id, 10));

        // 測試庫存剛好的情況
        $this->assertTrue($this->lotService->checkInventory($lot->id, 5));
    }

    /** @test */
    public function it_does_not_check_inventory_for_auction_product()
    {
        // 創建一個競標商品（status < 60）
        $lot = Lot::factory()->create([
            'status' => 21, // 競標成功
            'inventory' => 1,
            'owner_id' => $this->user->id,
        ]);

        // 競標商品不檢查庫存，應該總是返回 true
        $this->assertTrue($this->lotService->checkInventory($lot->id, 999));
    }

    /** @test */
    public function it_can_deduct_inventory_for_direct_sale_product()
    {
        // 創建一個直賣商品
        $lot = Lot::factory()->create([
            'status' => 61,
            'inventory' => 10,
            'owner_id' => $this->user->id,
        ]);

        // 扣減庫存
        $this->assertTrue($this->lotService->deductInventory($lot->id, 3));

        // 重新載入商品資料
        $lot->refresh();

        // 檢查庫存是否正確扣減
        $this->assertEquals(7, $lot->inventory);
    }

    /** @test */
    public function it_cannot_deduct_more_than_available_inventory()
    {
        // 創建一個直賣商品
        $lot = Lot::factory()->create([
            'status' => 61,
            'inventory' => 5,
            'owner_id' => $this->user->id,
        ]);

        // 嘗試扣減超過庫存的數量
        $this->assertFalse($this->lotService->deductInventory($lot->id, 10));

        // 重新載入商品資料
        $lot->refresh();

        // 檢查庫存沒有被扣減
        $this->assertEquals(5, $lot->inventory);
    }

    /** @test */
    public function it_can_check_and_deduct_multiple_inventory()
    {
        // 創建兩個直賣商品
        $lot1 = Lot::factory()->create([
            'status' => 61,
            'inventory' => 5,
            'owner_id' => $this->user->id,
        ]);

        $lot2 = Lot::factory()->create([
            'status' => 61,
            'inventory' => 3,
            'owner_id' => $this->user->id,
        ]);

        $items = [
            ['lot_id' => $lot1->id, 'quantity' => 2],
            ['lot_id' => $lot2->id, 'quantity' => 1],
        ];

        // 檢查並扣減庫存
        $result = $this->lotService->checkAndDeductInventory($items);

        $this->assertTrue($result['success']);

        // 重新載入商品資料
        $lot1->refresh();
        $lot2->refresh();

        // 檢查庫存是否正確扣減
        $this->assertEquals(3, $lot1->inventory);
        $this->assertEquals(2, $lot2->inventory);
    }

    /** @test */
    public function it_returns_error_when_inventory_insufficient()
    {
        // 創建兩個直賣商品
        $lot1 = Lot::factory()->create([
            'status' => 61,
            'inventory' => 5,
            'owner_id' => $this->user->id,
        ]);

        $lot2 = Lot::factory()->create([
            'status' => 61,
            'inventory' => 1,
            'owner_id' => $this->user->id,
        ]);

        $items = [
            ['lot_id' => $lot1->id, 'quantity' => 2],
            ['lot_id' => $lot2->id, 'quantity' => 3], // 超過庫存
        ];

        // 檢查並扣減庫存
        $result = $this->lotService->checkAndDeductInventory($items);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('庫存不足', $result['message']);
        $this->assertCount(1, $result['insufficient_items']);
        $this->assertEquals($lot2->id, $result['insufficient_items'][0]['lot_id']);

        // 重新載入商品資料，確認庫存沒有被扣減
        $lot1->refresh();
        $lot2->refresh();

        $this->assertEquals(5, $lot1->inventory);
        $this->assertEquals(1, $lot2->inventory);
    }
}
