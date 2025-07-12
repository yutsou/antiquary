# 庫存檢查功能實現說明

## 功能概述

本次實現了在下單時檢查庫存並扣減庫存的功能，確保不會超賣商品。

## 實現的功能

### 1. LotService 新增方法

在 `app/Services/LotService.php` 中新增了以下方法：

- `checkInventory($lotId, $quantity)` - 檢查單個商品庫存是否足夠
- `checkMultipleInventory($items)` - 檢查多個商品庫存是否足夠
- `deductInventory($lotId, $quantity)` - 扣減單個商品庫存
- `deductMultipleInventory($items)` - 扣減多個商品庫存
- `checkAndDeductInventory($items)` - 原子操作：檢查並扣減庫存

### 2. 庫存檢查邏輯

- **直賣商品**（status >= 60）：會檢查和扣減庫存
- **競標商品**（status < 60）：不檢查庫存，因為競標商品通常是單件商品

### 3. 訂單創建時加入庫存檢查

修改了以下訂單創建方法：

- `OrderService::createCartOrder()` - 購物車結帳
- `OrderService::createMergeShippingOrder()` - 合併運費訂單
- `OrderService::createProductOrder()` - 直接購買商品

### 4. 異常處理

在以下 Controller 方法中加入異常處理：

- `MemberController::cartConfirm()` - 購物車確認
- `MemberController::mergeShippingConfirm()` - 合併運費確認
- `MemberController::confirmProduct()` - 商品確認

### 5. 前端錯誤顯示

在以下頁面加入錯誤訊息顯示：

- `resources/views/account/cart/show.blade.php` - 購物車頁面
- `resources/views/account/products/check.blade.php` - 商品確認頁面
- `resources/views/account/cart/merge_shipping/check.blade.php` - 合併運費確認頁面

## 使用方式

### 基本使用

```php
// 檢查單個商品庫存
$lotService = app(LotService::class);
$hasStock = $lotService->checkInventory($lotId, $quantity);

// 扣減庫存
$success = $lotService->deductInventory($lotId, $quantity);

// 檢查並扣減多個商品庫存（推薦使用）
$items = [
    ['lot_id' => 1, 'quantity' => 2],
    ['lot_id' => 2, 'quantity' => 1],
];
$result = $lotService->checkAndDeductInventory($items);

if (!$result['success']) {
    // 處理庫存不足的情況
    $insufficientItems = $result['insufficient_items'];
    // 顯示錯誤訊息
}
```

### 錯誤處理

當庫存不足時，系統會拋出異常並顯示詳細的錯誤訊息：

```
以下商品庫存不足：
商品名稱 - 需要 5 件，庫存 3 件
```

## 測試

創建了完整的測試檔案 `tests/Unit/LotServiceInventoryTest.php`，包含以下測試：

- 直賣商品庫存檢查
- 競標商品不檢查庫存
- 庫存扣減功能
- 庫存不足時的錯誤處理
- 多商品庫存檢查和扣減
- 原子操作測試

運行測試：
```bash
php artisan test tests/Unit/LotServiceInventoryTest.php
```

## 注意事項

1. **競標商品**：競標商品（status < 60）不會檢查庫存，因為通常是單件商品
2. **直賣商品**：只有直賣商品（status >= 60）才會檢查和扣減庫存
3. **原子操作**：建議使用 `checkAndDeductInventory()` 方法，它會先檢查所有商品庫存，確認足夠後才進行扣減
4. **錯誤處理**：所有訂單創建方法都加入了異常處理，會將庫存不足的錯誤訊息顯示給用戶

## 資料庫結構

確保 `lots` 表有 `inventory` 欄位：

```sql
ALTER TABLE lots ADD COLUMN inventory INT DEFAULT 1 AFTER type;
```

這個欄位在 `database/migrations/2025_06_28_123800_add_inventory_to_lots_table.php` 中已經定義。 
