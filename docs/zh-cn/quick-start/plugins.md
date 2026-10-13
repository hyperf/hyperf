# IDE 插件

## PhpStorm 插件

### 数据库插件

可以在 PhpStorm 中安装 [Hyperf Query](https://plugins.jetbrains.com/plugin/33333-hyperf-query) 插件，为 Hyperf 查询构造器提供数据库集成支持。它配合 DataGrip 为数据库 schema、表、视图和列提供自动补全，主要功能如下：

- 查询构造器和 Schema 构造器方法中 schema、表、视图、列的补全
- 迁移文件（migration）中的补全
- 未知数据库元素的检查（Inspection）
- 表别名支持
- 从模型解析构造器方法的表名
- 关联模型闭包方法中的表名解析
- 数据库元素的文本跳转（Ctrl+Click）
- 可配置的表前缀与数据源过滤

安装方式：`Preferences` > `Plugins` > `Marketplace` 搜索 **Hyperf Query**，点击 **Install Plugin** 安装。安装后建议先完成数据源配置，并在插件设置中过滤要使用的数据源。

> 该项目为 [laravel-query-intellij](https://github.com/ekvedaras/laravel-query-intellij)（MIT 协议）的适配分支，已将识别目标从 `Illuminate\Database\*` 迁移到 `Hyperf\Database\*`，源码位于 [hyperf-query-intellij](https://github.com/tw2066/hyperf-query-intellij)。
