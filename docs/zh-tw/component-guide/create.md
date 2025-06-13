# 建立新的元件

`Hyperf` 官方提供了工具來快速建立元件包。

```
# 建立適配 Hyperf 最新版本的元件包
composer create-project hyperf/component-creator your_component dev-master

# 建立適配 Hyperf 2.0 版本的元件包
composer create-project hyperf/component-creator your_component "2.0.*"
```

## 在專案中使用未釋出的元件包

假設專案目錄如下

```
/opt/project // 專案目錄
/opt/your_component // 元件包目錄
```

假設元件名為 `your_component/your_component`

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







