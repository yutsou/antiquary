# Sitemap 功能說明

## 概述
這個項目已經配置了自動 sitemap 生成功能，使用 `spatie/laravel-sitemap` 套件。

## 功能特點

### 1. 自動生成 Sitemap
- 當有新的拍賣品（Lot）創建時，會自動重新生成 sitemap
- 當拍賣品狀態更新為已發布狀態時，會自動重新生成 sitemap
- 當拍賣品被刪除時，會自動重新生成 sitemap

### 2. 包含的頁面
- 首頁 (`/`)
- 所有已發布的拍賣品頁面 (`/lots/{id}`)
- 靜態頁面（關於我們、隱私政策等）

### 3. 定時任務
- 每天凌晨 2 點自動生成 sitemap
- 避免重複執行
- 在背景運行

## 使用方法

### 手動生成 Sitemap
```bash
php artisan sitemap:generate
```

### 查看生成的 Sitemap
訪問：`https://your-domain.com/sitemap.xml`

### 設置定時任務
確保你的服務器有 cron 任務運行：
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 文件結構

### 控制器
- `app/Http/Controllers/SitemapController.php` - 處理 sitemap 請求

### 視圖
- `resources/views/sitemap.blade.php` - sitemap XML 模板

### 命令
- `app/Console/Commands/GenerateSitemap.php` - 生成 sitemap 的命令

### 觀察者
- `app/Observers/LotObserver.php` - 監聽 Lot 模型變化

### 定時任務
- `app/Console/Kernel.php` - 定義定時任務

## 配置說明

### 拍賣品狀態
系統會包含以下狀態的拍賣品：
- 狀態 20: 拍賣中
- 狀態 21: 拍賣結束
- 狀態 61: 已上架

### 自動更新觸發條件
1. 新拍賣品創建
2. 拍賣品狀態變更為已發布狀態
3. 拍賣開始時間更新
4. 拍賣品刪除

## 故障排除

### 檢查 Sitemap 是否正常生成
```bash
php artisan sitemap:generate
```

### 檢查日誌
如果自動生成失敗，查看 Laravel 日誌：
```bash
tail -f storage/logs/laravel.log
```

### 手動測試觀察者
創建一個測試拍賣品來測試自動更新功能。

## 注意事項

1. 確保 `public` 目錄有寫入權限
2. 如果使用 CDN，可能需要手動更新 CDN 緩存
3. 大量拍賣品時，生成可能需要一些時間
4. 建議在生產環境中監控 sitemap 生成過程
