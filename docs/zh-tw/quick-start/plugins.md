# IDE 外掛

## PhpStorm 外掛

### 資料庫外掛

可以在 PhpStorm 中安裝 [Hyperf Query](https://plugins.jetbrains.com/plugin/33333-hyperf-query) 外掛，為 Hyperf 查詢構造器提供資料庫整合支援。它配合 DataGrip 為資料庫 schema、表、檢視和列提供自動補全，主要功能如下：

- 查詢構造器和 Schema 構造器方法中 schema、表、檢視、列的補全
- 遷移檔案（migration）中的補全
- 未知資料庫元素的檢查（Inspection）
- 表別名支援
- 從模型解析構造器方法的表名
- 關聯模型閉包方法中的表名解析
- 資料庫元素的文字跳轉（Ctrl+Click）
- 可配置的表字首與資料來源過濾

安裝方式：`Preferences` > `Plugins` > `Marketplace` 搜尋 **Hyperf Query**，點選 **Install Plugin** 安裝。安裝後建議先完成資料來源配置，並在外掛設定中過濾要使用的資料來源。

> 該專案為 [laravel-query-intellij](https://github.com/ekvedaras/laravel-query-intellij)（MIT 協議）的適配分支，已將識別目標從 `Illuminate\Database\*` 遷移到 `Hyperf\Database\*`，原始碼位於 [hyperf-query-intellij](https://github.com/tw2066/hyperf-query-intellij)。
