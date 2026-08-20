# IDE 插件

## PhpStorm 插件

### Hyperf Base 插件

可以在 PhpStorm 中安装 [Hyperf Base](https://github.com/tw2066/idea-plugin-hyperf) 插件，为 Hyperf 框架提供代码补全、跳转与快捷命令支持，主要功能如下：

- 路由：`Router::get/post/...` 中 `Controller@action` 的补全与跳转
- 配置键：`config()` 辅助函数与 `ConfigInterface::get()/has()` 的键索引、补全与跳转（支持 3.1+ 子目录与点号文件名）
- 翻译键：`trans()` / `__()` 与 `TranslatorInterface::trans()` 的键索引、补全与跳转
- 环境变量：`env()` 键的补全与跳转（索引项目 `.env` 文件）
- 验证规则：`FormRequest::rules()`、`ValidatorFactory::make()/validate()`、`$scenes` 中规则字符串的补全与悬停中文文档
- BASE_PATH 路径：`BASE_PATH . '/a/b'` 拼接链中目录/文件的补全与跳转
- 视图模板：`view()`、`RenderInterface::render()` 等模板名的补全与跳转（点语法 + `pkg::name` 命名空间）
- AOP 切面：`#[Aspect]` 注解与 `AbstractAspect` 属性中 `'FQN::method'` 字符串的跳转与方法名补全
- 缓存监听器：`#[Cacheable(listener: "...")]` 与 `DeleteListenerEvent` 监听器名的补全与互跳
- DI 接口绑定：悬停接口时文档弹窗显示当前生效的实现类链接
- Crontab：`callback` 方法名补全与跳转；`rule` 表达式悬停显示最近 5 次执行时间
- Hyperf 顶级菜单：代码生成（`gen:*`）与常用命令（`migrate`、`start`、`describe:routes` 等）一键在内置 Terminal 执行
- 命令类行标记：`Hyperf\Command\Command` 子类类名旁的运行按钮，点击直接执行命令

> 仅支持 PhpStorm 2026.2 及以上版本

### 数据库插件

可以在 PhpStorm 中安装 [Hyperf Query](https://github.com/tw2066/hyperf-query-intellij) 插件，为 Hyperf 查询构造器提供数据库集成支持。它配合 DataGrip 为数据库 schema、表、视图和列提供自动补全，主要功能如下：

- 查询构造器和 Schema 构造器方法中 schema、表、视图、列的补全
- 迁移文件（migration）中的补全
- 未知数据库元素的检查（Inspection）
- 表别名支持
- 从模型解析构造器方法的表名
- 关联模型闭包方法中的表名解析
- 数据库元素的文本跳转（Ctrl+Click）
- 可配置的表前缀与数据源过滤

安装方式：`Preferences` > `Plugins` > `Marketplace` 搜索 **Hyperf Base** / **Hyperf Query**，点击 **Install Plugin** 安装。安装后建议先完成数据源配置，并在插件设置中过滤要使用的数据源。
