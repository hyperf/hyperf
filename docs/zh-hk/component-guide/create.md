# 創建新的組件

`Hyperf` 官方提供了工具來快速創建組件包。

```
# 創建適配 Hyperf 最新版本的組件包
composer create-project hyperf/component-creator your_component dev-master

# 創建適配 Hyperf 2.0 版本的組件包
composer create-project hyperf/component-creator your_component "2.0.*"
```

## 在項目中使用未發佈的組件包

假設項目目錄如下

```
/opt/project // 項目目錄
/opt/your_component // 組件包目錄
```

假設組件名為 `your_component/your_component`

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

最後在目錄 `/opt/project` 中執行 `composer update -o` 即可。







