# 查詢分頁

在使用 [hyperf/database](https://github.com/hyperf/database) 來查詢資料時，可以很方便的通過與 [hyperf/paginator](https://github.com/hyperf/paginator) 元件配合便捷地對查詢結果進行分頁。

# 使用方法

在您通過 [查詢構造器](zh/db/querybuilder.md) 或 [模型](zh/db/model.md) 查詢資料時，可以通過 `paginate` 方法來處理分頁，該方法會自動根據使用者正在檢視的頁面來設定限制和偏移量，預設情況下，通過當前 HTTP 請求所帶的 `page` 引數的值來檢測當前的頁數：

> 由於 Hyperf 當前並不支援檢視，所以分頁元件尚未支援對檢視的渲染，直接返回分頁結果預設會以 application/json 格式輸出。

## 查詢構造器分頁

```php
<?php
// 展示應用中的所有使用者，每頁顯示 10 條資料
return Db::table('users')->paginate(10);
```

## 模型分頁 

您可以直接通過靜態方法呼叫 `paginate` 方法來進行分頁：

```php
<?php
// 展示應用中的所有使用者，每頁顯示 10 條資料
return User::paginate(10);
```

當然您也可以設定查詢的條件或其它查詢的設定方法：

```php
<?php 
// 展示應用中的所有使用者，每頁顯示 10 條資料
return User::where('gender', 1)->paginate(10);
```

## 分頁器例項方法

這裡僅說明分頁器在資料庫查詢上的使用方法，更多關於分頁器的細節可閱讀 [分頁](zh/paginator.md) 章節。