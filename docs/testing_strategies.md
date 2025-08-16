# Laravel 測試策略說明

## 測試中的資料庫處理方式

### 1. RefreshDatabase（預設）
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;
}
```

**特點：**
- ✅ 每個測試前清空資料庫
- ✅ 重新執行所有 migration
- ✅ 測試完全隔離
- ❌ 會清空所有資料

**適用場景：**
- 單元測試
- 需要乾淨環境的測試
- 測試資料庫結構變更

### 2. DatabaseTransactions（推薦）
```php
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MyTest extends TestCase
{
    use DatabaseTransactions;
}
```

**特點：**
- ✅ 測試後自動回滾所有變更
- ✅ 不會清空現有資料
- ✅ 測試執行快速
- ✅ 保持資料庫狀態

**適用場景：**
- 大部分單元測試
- 不想影響現有資料的測試
- 快速測試執行

### 3. DatabaseMigrations
```php
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MyTest extends TestCase
{
    use DatabaseMigrations;
}
```

**特點：**
- ✅ 每個測試前執行 migration
- ✅ 測試後清空資料
- ❌ 執行較慢

**適用場景：**
- 需要測試 migration 的場景
- 測試資料庫結構

## 配置測試專用資料庫

### 1. 修改 phpunit.xml
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_DATABASE" value="antiquary_testing"/>
    <!-- 其他設定 -->
</php>
```

### 2. 在 .env.testing 中配置
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=antiquary_testing
DB_USERNAME=root
DB_PASSWORD=
```

### 3. 創建測試資料庫
```bash
# 創建測試資料庫
mysql -u root -p -e "CREATE DATABASE antiquary_testing;"

# 執行 migration 到測試資料庫
php artisan migrate --env=testing
```

## 最佳實踐

### 1. 選擇合適的 Trait
```php
// 對於單元測試，使用 DatabaseTransactions
class UnitTest extends TestCase
{
    use DatabaseTransactions;
}

// 對於需要測試資料庫結構的測試，使用 RefreshDatabase
class MigrationTest extends TestCase
{
    use RefreshDatabase;
}
```

### 2. 使用 Factory 創建測試資料
```php
// 創建 User Factory
php artisan make:factory UserFactory

// 在測試中使用
$user = User::factory()->create();
```

### 3. 使用 Seeder 準備測試資料
```php
// 創建測試 Seeder
php artisan make:seeder TestDataSeeder

// 在測試中使用
$this->seed(TestDataSeeder::class);
```

## 當前專案的測試配置

### 已修改的測試檔案
1. `tests/Unit/OrderRecordTest.php` - 改用 DatabaseTransactions
2. `tests/Unit/RefundRemarkTest.php` - 改用 DatabaseTransactions

### 測試執行
```bash
# 運行特定測試（不會清空資料庫）
php artisan test tests/Unit/OrderRecordTest.php

# 運行所有單元測試
php artisan test tests/Unit/

# 運行所有測試
php artisan test
```

## 注意事項

1. **DatabaseTransactions** 是最佳選擇，因為：
   - 不會清空現有資料
   - 測試執行快速
   - 自動回滾變更

2. **RefreshDatabase** 只在以下情況使用：
   - 測試 migration 變更
   - 需要完全乾淨的環境
   - 測試資料庫結構

3. **測試資料庫** 建議：
   - 使用獨立的測試資料庫
   - 定期備份重要資料
   - 使用環境變數區分測試和生產環境

## 故障排除

### 問題：測試後資料被清空
**解決方案：** 改用 `DatabaseTransactions`

### 問題：測試執行太慢
**解決方案：** 使用 `DatabaseTransactions` 而不是 `RefreshDatabase`

### 問題：測試間相互影響
**解決方案：** 確保每個測試都使用唯一的資料，或使用 `RefreshDatabase`
