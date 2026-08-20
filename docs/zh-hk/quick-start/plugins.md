# IDE 插件

## PhpStorm 插件

### Hyperf Base 插件

可以在 PhpStorm 中安裝 [Hyperf Base](https://github.com/tw2066/idea-plugin-hyperf) 插件，為 Hyperf 框架提供代碼補全、跳轉與快捷命令支持，主要功能如下：

- 路由：`Router::get/post/...` 中 `Controller@action` 的補全與跳轉
- 配置鍵：`config()` 輔助函數與 `ConfigInterface::get()/has()` 的鍵索引、補全與跳轉（支持 3.1+ 子目錄與點號文件名）
- 翻譯鍵：`trans()` / `__()` 與 `TranslatorInterface::trans()` 的鍵索引、補全與跳轉
- 環境變量：`env()` 鍵的補全與跳轉（索引項目 `.env` 文件）
- 驗證規則：`FormRequest::rules()`、`ValidatorFactory::make()/validate()`、`$scenes` 中規則字符串的補全與懸停中文文檔
- BASE_PATH 路徑：`BASE_PATH . '/a/b'` 拼接鏈中目錄/文件的補全與跳轉
- 視圖模板：`view()`、`RenderInterface::render()` 等模板名的補全與跳轉（點語法 + `pkg::name` 命名空間）
- AOP 切面：`#[Aspect]` 註解與 `AbstractAspect` 屬性中 `'FQN::method'` 字符串的跳轉與方法名補全
- 緩存監聽器：`#[Cacheable(listener: "...")]` 與 `DeleteListenerEvent` 監聽器名的補全與互跳
- DI 接口綁定：懸停接口時文檔彈窗顯示當前生效的實現類鏈接
- Crontab：`callback` 方法名補全與跳轉；`rule` 表達式懸停顯示最近 5 次執行時間
- Hyperf 頂級菜單：代碼生成（`gen:*`）與常用命令（`migrate`、`start`、`describe:routes` 等）一鍵在內置 Terminal 執行
- 命令類行標記：`Hyperf\Command\Command` 子類類名旁的運行按鈕，點擊直接執行命令

> 僅支持 PhpStorm 2026.2 及以上版本

### 數據庫插件

可以在 PhpStorm 中安裝 [Hyperf Query](https://github.com/tw2066/hyperf-query-intellij) 插件，為 Hyperf 查詢構造器提供數據庫集成支持。它配合 DataGrip 為數據庫 schema、表、視圖和列提供自動補全，主要功能如下：

- 查詢構造器和 Schema 構造器方法中 schema、表、視圖、列的補全
- 遷移文件（migration）中的補全
- 未知數據庫元素的檢查（Inspection）
- 表別名支持
- 從模型解析構造器方法的表名
- 關聯模型閉包方法中的表名解析
- 數據庫元素的文本跳轉（Ctrl+Click）
- 可配置的表前綴與數據源過濾

安裝方式：`Preferences` > `Plugins` > `Marketplace` 搜索 **Hyperf Base** / **Hyperf Query**，點擊 **Install Plugin** 安裝。安裝後建議先完成數據源配置，並在插件設置中過濾要使用的數據源。
