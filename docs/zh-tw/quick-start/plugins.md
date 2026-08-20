# IDE 外掛

## PhpStorm 外掛

### Hyperf Base 外掛

可以在 PhpStorm 中安裝 [Hyperf Base](https://github.com/tw2066/idea-plugin-hyperf) 外掛，為 Hyperf 框架提供程式碼補全、跳轉與快捷命令支援，主要功能如下：

- 路由：`Router::get/post/...` 中 `Controller@action` 的補全與跳轉
- 設定鍵：`config()` 輔助函式與 `ConfigInterface::get()/has()` 的鍵索引、補全與跳轉（支援 3.1+ 子目錄與點號檔名）
- 翻譯鍵：`trans()` / `__()` 與 `TranslatorInterface::trans()` 的鍵索引、補全與跳轉
- 環境變數：`env()` 鍵的補全與跳轉（索引專案 `.env` 檔案）
- 驗證規則：`FormRequest::rules()`、`ValidatorFactory::make()/validate()`、`$scenes` 中規則字串的補全與懸停中文文件
- BASE_PATH 路徑：`BASE_PATH . '/a/b'` 拼接鏈中目錄/檔案的補全與跳轉
- 視圖模板：`view()`、`RenderInterface::render()` 等模板名的補全與跳轉（點語法 + `pkg::name` 命名空間）
- AOP 切面：`#[Aspect]` 註解與 `AbstractAspect` 屬性中 `'FQN::method'` 字串的跳轉與方法名稱補全
- 快取監聽器：`#[Cacheable(listener: "...")]` 與 `DeleteListenerEvent` 監聽器名的補全與互跳
- DI 介面綁定：懸停介面時文件彈窗顯示當前生效的實作類別連結
- Crontab：`callback` 方法名稱補全與跳轉；`rule` 表達式懸停顯示最近 5 次執行時間
- Hyperf 頂層選單：程式碼產生（`gen:*`）與常用命令（`migrate`、`start`、`describe:routes` 等）一鍵在內建 Terminal 執行
- 命令類別行標記：`Hyperf\Command\Command` 子類別名稱旁的執行按鈕，點選直接執行命令

> 僅支援 PhpStorm 2026.2 及以上版本

### 資料庫外掛

可以在 PhpStorm 中安裝 [Hyperf Query](https://github.com/tw2066/hyperf-query-intellij) 外掛，為 Hyperf 查詢構造器提供資料庫整合支援。它配合 DataGrip 為資料庫 schema、表、檢視和列提供自動補全，主要功能如下：

- 查詢構造器和 Schema 構造器方法中 schema、表、檢視、列的補全
- 遷移檔案（migration）中的補全
- 未知資料庫元素的檢查（Inspection）
- 表別名支援
- 從模型解析構造器方法的表名
- 關聯模型閉包方法中的表名解析
- 資料庫元素的文字跳轉（Ctrl+Click）
- 可配置的表字首與資料來源過濾

安裝方式：`Preferences` > `Plugins` > `Marketplace` 搜尋 **Hyperf Base** / **Hyperf Query**，點選 **Install Plugin** 安裝。安裝後建議先完成資料來源配置，並在外掛設定中過濾要使用的資料來源。
