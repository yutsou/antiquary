# 動態 Sitemap 說明

## 🎯 問題解決

你提出的問題非常重要！當 `public/sitemap.xml` 靜態文件存在時，Web 服務器會直接提供這個文件，而不會執行 Laravel 的 `SitemapController`。這意味著：

- ❌ 靜態文件無法包含最新的拍賣品數據
- ❌ 新創建的拍賣品不會出現在 sitemap 中
- ❌ 需要手動更新靜態文件

## ✅ 解決方案：動態 Sitemap

我們已經將系統改為使用**動態 sitemap**：

### 工作原理
1. 當訪問 `/sitemap.xml` 時，Laravel 路由會執行 `SitemapController@index`
2. 控制器實時查詢數據庫獲取最新的拍賣品數據
3. 使用 `sitemap.blade.php` 模板生成 XML 內容
4. 返回最新的 sitemap 內容

### 優勢
- ✅ **實時數據**：每次訪問都包含最新的拍賣品
- ✅ **無需手動更新**：系統自動提供最新內容
- ✅ **自動包含新拍賣品**：新創建的拍賣品立即出現在 sitemap 中
- ✅ **無文件權限問題**：不需要寫入 public 目錄

### 性能考慮
- 每次訪問都會查詢數據庫（約 218 個拍賣品）
- 如果性能成為問題，可以添加緩存：
  ```php
  $lots = Cache::remember('sitemap_lots', 3600, function () {
      return Lot::whereIn('status', [20, 21, 61])->latest()->get();
  });
  ```

## 🚀 使用方法

### 正常使用
- 直接訪問：`https://your-domain.com/sitemap.xml`
- 無需任何手動操作
- 內容始終是最新的

### 監控
- 檢查日誌：`tail -f storage/logs/laravel.log`
- 當有拍賣品變化時，會記錄日誌

### 可選：生成靜態文件
如果需要靜態文件（例如用於 CDN），可以運行：
```bash
php artisan sitemap:generate --static
```

## 🎉 結果

現在你的 sitemap 會：
- 自動包含所有新創建的拍賣品
- 實時反映拍賣品狀態變化
- 無需任何手動維護
- 為搜索引擎提供最新的網站結構信息
