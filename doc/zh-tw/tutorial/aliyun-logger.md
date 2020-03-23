# 阿里雲日誌服務

在 `Docker 叢集` 部署專案時，收集日誌會是一個比較麻煩的問題，但阿里雲提供了十分好用的 `日誌收集系統`，本篇文件就是簡略介紹一下阿里雲日誌收集的使用方法。

* [Docker Swarm 叢集搭建](zh-tw/tutorial/docker-swarm.md)

## 開通日誌服務

首先第一步便是在阿里雲上開通 `日誌服務`。

[日誌服務文件](https://help.aliyun.com/product/28958.html)

以下的教程是一個順序的操作方式，一步一步講述如何使用日誌服務。

## 安裝 Logtail 容器

[標準 Docker 日誌採集流程文件](https://help.aliyun.com/document_detail/66659.html)

|                 引數                  |                    說明                    |
|:-------------------------------------:|:------------------------------------------:|
|          ${your_region_name}          |     區域 ID 比如華東 1 區域是 cn-hangzhou     |
|        ${your_aliyun_user_id}         | 使用者標識，請替換為您的阿里雲主賬號使用者 ID。 |
| ${your_machine_group_user_defined_id} |   叢集的機器組自定義標識 以下使用 Hyperf   |

```
docker run -d -v /:/logtail_host:ro -v /var/run/docker.sock:/var/run/docker.sock \
--env ALIYUN_LOGTAIL_CONFIG=/etc/ilogtail/conf/${your_region_name}/ilogtail_config.json \
--env ALIYUN_LOGTAIL_USER_ID=${your_aliyun_user_id} \
--env ALIYUN_LOGTAIL_USER_DEFINED_ID=${your_machine_group_user_defined_id} \
registry.cn-hangzhou.aliyuncs.com/log-service/logtail
```

## 配置日誌收集

### 建立 Project

登入阿里雲日誌服務，點選 `建立 Project`，填寫以下資訊

|     引數     |     填寫示例     |
|:------------:|:----------------:|
| Project 名稱  |      hyperf      |
|     註釋     | 用於日誌系統演示 |
|   所屬區域   |  華東 1（杭州）   |
|   開通服務   |     詳細日誌     |
| 日誌儲存位置 |   當前 Project    |

### 建立 Logstore

除以下引數，按需填寫，其他都使用預設即可

|     引數     |     填寫示例     |
|:------------:|:---------------:|
| Logstore 名稱 | hyperf-demo-api |
|   永久儲存    |      false      |
| 資料儲存時間  |       60        |

### 接入資料

1. 選擇 Docker 檔案

2. 建立機器組

如果已經建立過機器組，可以跳過這一步

|      引數      |    填寫示例    |
|:-------------:|:-------------:|
|   機器組名稱    |     Hyperf    |
|   機器組標識    |  使用者自定義標識 |
|  使用者自定義標識  |     Hyperf    |

3. 配置機器組

應用剛剛建立的機器組

4. 配置 Logtail

`Label` 白名單，這裡可以按需填寫，以下按照專案名字來配置，而專案名會在 Docker 容器執行時設定。

|      引數      |                     填寫示例                      |    填寫示例     |
|:--------------:|:-------------------------------------------------:|:---------------:|
|    配置名稱    |                  hyperf-demo-api                  |                 |
|    日誌路徑    |               /opt/www/runtime/logs               |      *.log      |
|  Label 白名單   |                     app.name                      | hyperf-demo-api |
|      模式      |                   完整正則模式                    |                 |
|    單行模式    |                       false                       |                 |
|    日誌樣例    |     `[2019-03-07 11:58:57] hyperf.WARNING: xxx`     |                 |
| 首行正則表示式 |         \[\d+-\d+-\d+\s\d+:\d+:\d+\]\s.*          |                 |
|    提取欄位    |                       true                        |                 |
|   正則表示式   | \[(\d+-\d+-\d+\s\d+:\d+:\d+)\]\s(\w+)\.(\w+):(.*) |                 |
|  日誌抽取內容  |              time name level content              |                 |

5. 查詢分析配置

欄位索引屬性

| 欄位名稱 | 型別 |  別名   | 中文分詞 | 開啟統計 |
|:--------:|:----:|:-------:|:--------:|:--------:|
|   name   | text |  name   |  false   |   true   |
|  level   | text |  level  |  false   |   true   |
|   time   | text |  time   |  false   |  false   |
| content  | text | content |   true   |  false   |

### 執行映象

執行映象時，只需要設定 Container `labels` 即可。

|   name   |      value      |
|:--------:|:---------------:|
| app.name | hyperf-demo-api |

比如以下 Dockerfile

```Dockerfile
# Default Dockerfile

FROM hyperf/hyperf:7.2-alpine-cli
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="hyperf-demo-api"

# 其它內容省略
```

## 注意事項

- Docker 儲存驅動限制：目前只支援 `overlay`、`overlay2`，其他儲存驅動需將日誌所在目錄 `mount` 到本地，然後改為收集宿主機 `~/logtail_host/your_path` 下的日誌即可。



