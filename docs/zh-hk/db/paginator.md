# 查詢分頁

在使用 [hyperf/database](https://github.com/hyperf/database) 來查詢數據時，可以很方便的通過與 [hyperf/paginator](https://github.com/hyperf/paginator) 組件配合便捷地對查詢結果進行分頁。

# 使用方法

在您通過 [查詢構造器](zh-hk/db/querybuilder.md) 或 [模型](zh-hk/db/model.md) 查詢數據時，可以通過 `paginate` 方法來處理分頁，該方法會自動根據用户正在查看的頁面來設置限制和偏移量，默認情況下，通過當前 HTTP 請求所帶的 `page` 參數的值來檢測當前的頁數：

> 由於 Hyperf 當前並不支持視圖，所以分頁組件尚未支持對視圖的渲染，直接返回分頁結果默認會以 application/json 格式輸出。

## 查詢構造器分頁

```php
<?php
// 展示應用中的所有用户，每頁顯示 10 條數據
return Db::table('users')->paginate(10);
```

## 模型分頁 

您可以直接通過靜態方法調用 `paginate` 方法來進行分頁：

```php
<?php
// 展示應用中的所有用户，每頁顯示 10 條數據
return User::paginate(10);
```

當然您也可以設置查詢的條件或其它查詢的設置方法：

```php
<?php 
// 展示應用中的所有用户，每頁顯示 10 條數據
return User::where('gender', 1)->paginate(10);
```

## 分頁器實例方法

這裏僅説明分頁器在數據庫查詢上的使用方法，更多關於分頁器的細節可閲讀 [分頁](zh-hk/paginator.md) 章節。