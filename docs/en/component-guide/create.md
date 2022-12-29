# 创建新的组件

`Hyperf` 官方提供了工具来快速创建组件包。

```
# 创建适配 Hyperf 最新版本的组件包
composer create-project hyperf/component-creator your_component dev-master

# 创建适配 Hyperf 2.0 版本的组件包
composer create-project hyperf/component-creator your_component "2.0.*"
```

## 在项目中使用未发布的组件包

假设项目目录如下

```
/opt/project // 项目目录
/opt/your_component // 组件包目录
```

假设组件名为 `your_component/your_component`

修改 /opt/project/composer.json

> 以下省略其他不相干的配置

```json
{
    "require": {
        "your_component/your_component": "dev-master"
    },
    "repositories": {
        "your_component": {
            "type": "path",
            "url": "/opt/your_component"
        }
    }
}
```

最后在目录 `/opt/project` 中执行 `composer update -o` 即可。







