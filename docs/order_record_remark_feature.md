# OrderRecord 備注功能說明

## 概述

在 `order_records` 表中新增了 `remark` 欄位，用於儲存訂單狀態變更時的備注資訊。

## 資料庫變更

### 新增欄位
- 欄位名稱：`remark`
- 資料類型：`TEXT`
- 允許空值：是
- 位置：在 `status` 欄位之後

### Migration 檔案
```php
// database/migrations/2025_08_16_054304_add_remark_to_order_records_table.php
Schema::table('order_records', function (Blueprint $table) {
    $table->text('remark')->nullable()->after('status')->comment('備注');
});
```

## 模型更新

### OrderRecord 模型
- 新增 `remark` 到 `$fillable` 陣列
- 支援直接創建和更新備注

```php
protected $fillable = [
    'order_id',
    'status',
    'remark'
];
```

## Repository 更新

### OrderRepository 方法更新
所有相關方法都新增了可選的 `$remark` 參數：

```php
// 更新訂單狀態
public function updateOrderStatus(int $status, $id, $remark = null)

// 更新訂單狀態並創建交易記錄
public function updateOrderStatusWithTransaction(array $data, int $status, $id, $remark = null)

// 創建訂單記錄
public function createOrderRecord(int $status, $id, $remark = null)
```

## Service 更新

### OrderService 方法更新
```php
public function updateOrderStatus($status, $order, $remark = null)
{
    return parent::updateOrderStatus($status, $order->id, $remark);
}
```

## 顯示功能

### OrderStatusPresenter 更新
在訂單詳情頁面中，如果訂單記錄有備注，會顯示在狀態下方：

```php
// 顯示備注
if ($orderRecord->remark) {
    $statusHtml = $statusHtml . '<br><span class="uk-text-meta">備注：' . $orderRecord->remark . '</span>';
}
```

## 使用範例

### 1. 基本使用
```php
// 創建帶備注的訂單記錄
OrderRecord::create([
    'order_id' => $order->id,
    'status' => 10,
    'remark' => '客戶要求延遲付款'
]);
```

### 2. 使用 Repository
```php
// 更新訂單狀態並加入備注
OrderRepository::updateOrderStatus(61, $orderId, '退款處理完成');
```

### 3. 使用 Service
```php
// 更新訂單狀態並加入備注
$this->orderService->updateOrderStatus(61, $order, '退款金額：NT$1,000，退款方式：銀行轉帳');
```

### 4. 實際應用 - 退款功能
在 `AuctioneerController::confirmRefund` 方法中：

```php
public function confirmRefund(Request $request, $orderId)
{
    // 準備備注內容
    $remark = '退款金額：NT$' . number_format($request->refund_amount);
    if ($request->refund_method == 'line_pay') {
        $remark .= '，退款方式：LINE Pay';
    } else {
        $remark .= '，退款方式：銀行轉帳';
    }
    
    // 如果有自定義備注，加入其中
    if ($request->filled('refund_remark')) {
        $remark .= '，備注：' . $request->refund_remark;
    }
    
    // 更新訂單狀態並記錄備注
    $this->orderService->updateOrderStatus(61, $order, $remark);
}
```

### 5. 前端界面 - 退款模態框
在退款模態框中新增了備注輸入欄位：

```html
<div class="uk-margin">
    <label class="uk-form-label">備注 (選填)</label>
    <div class="uk-form-controls">
        <textarea class="uk-textarea" name="refund_remark" rows="3" 
                  placeholder="請輸入退款備注，例如：客戶要求退款原因、特殊處理說明等"></textarea>
    </div>
</div>
```

### 6. JavaScript 處理
更新了 `orderAction.js` 中的 `confirm-refund` 函數來處理備注：

```javascript
$(document).on('click', '.confirm-refund', function(){
    let refundRemark = $('textarea[name="refund_remark"]').val();
    
    $.ajax({
        // ... 其他設定
        data: {
            refund_amount: refundAmount,
            refund_method: refundMethod,
            refund_remark: refundRemark
        },
        // ...
    });
});
```

## 測試

### 單元測試
創建了兩個測試檔案來測試備注功能：

#### OrderRecordTest.php
- 測試可以創建帶備注的訂單記錄
- 測試備注可以為空值

#### RefundRemarkTest.php
- 測試退款功能中的備注處理
- 測試備注格式的正確性

運行測試：
```bash
# 測試基本備注功能
php artisan test tests/Unit/OrderRecordTest.php

# 測試退款備注功能
php artisan test tests/Unit/RefundRemarkTest.php

# 運行所有測試
php artisan test tests/Unit/
```

## 注意事項

1. 備注欄位是可選的，不會影響現有功能
2. 所有相關方法都保持向後相容性
3. 備注內容會顯示在訂單詳情頁面的狀態記錄中
4. 建議在重要的狀態變更時加入有意義的備注

## 未來擴展

可以考慮以下擴展功能：
1. 備注的搜尋功能
2. 備注的編輯功能
3. 備注的權限控制
4. 備注的歷史記錄

