# IDE 插件

## PhpStorm 插件

### 數據庫插件

可以在 PhpStorm 中安裝 [Hyperf Query](https://plugins.jetbrains.com/plugin/33333-hyperf-query) 插件，為 Hyperf 查詢構造器提供數據庫集成支持。它配合 DataGrip 為數據庫 schema、表、視圖和列提供自動補全，主要功能如下：

- 查詢構造器和 Schema 構造器方法中 schema、表、視圖、列的補全
- 遷移文件（migration）中的補全
- 未知數據庫元素的檢查（Inspection）
- 表別名支持
- 從模型解析構造器方法的表名
- 關聯模型閉包方法中的表名解析
- 數據庫元素的文本跳轉（Ctrl+Click）
- 可配置的表前綴與數據源過濾

安裝方式：`Preferences` > `Plugins` > `Marketplace` 搜索 **Hyperf Query**，點擊 **Install Plugin** 安裝。安裝後建議先完成數據源配置，並在插件設置中過濾要使用的數據源。

> 該項目為 [laravel-query-intellij](https://github.com/ekvedaras/laravel-query-intellij)（MIT 協議）的適配分支，已將識別目標從 `Illuminate\Database\*` 遷移到 `Hyperf\Database\*`，源碼位於 [hyperf-query-intellij](https://github.com/tw2066/hyperf-query-intellij)。
