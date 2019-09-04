# 阿里云日志服务

在 `Docker集群` 部署项目时，收集日志会是一个比较麻烦的问题，但阿里云提供了十分好用的 `日志收集系统`，本篇文档就是简略介绍一下阿里云日志收集的使用方法。

* [Docker Swarm 集群搭建](zh/tutorial/docker-swarm.md)

## 开通日志服务

首先第一步便是在阿里云上开通 `日志服务`。

[文档](https://help.aliyun.com/product/28958.html?spm=a2c4g.11186623.6.540.12bac9b5C46s8N)

以下的教程是一个顺序的操作方式，一步一步讲述如何使用日志服务。

## 安装 Logtail容器

[文档](https://help.aliyun.com/document_detail/66659.html?spm=a2c4g.11186623.2.15.52d541865uv5Xr)

|                 参数                  |                        说明                         |
|:-------------------------------------:|:---------------------------------------------------:|
|          ${your_region_name}          | 区域ID 比如华东1区域是 cn-hangzhou |
|        ${your_aliyun_user_id}         |     用户标识，请替换为您的阿里云主账号用户ID。      |
| ${your_machine_group_user_defined_id} |      集群的机器组自定义标识，即为上述的 Hyperf      |

```
docker run -d -v /:/logtail_host:ro -v /var/run/docker.sock:/var/run/docker.sock \
--env ALIYUN_LOGTAIL_CONFIG=/etc/ilogtail/conf/${your_region_name}/ilogtail_config.json \
--env ALIYUN_LOGTAIL_USER_ID=${your_aliyun_user_id} \
--env ALIYUN_LOGTAIL_USER_DEFINED_ID=${your_machine_group_user_defined_id} \
registry.cn-hangzhou.aliyuncs.com/log-service/logtail
```

## 配置日志收集

### 创建 Project

登录阿里云日志服务，点击 `创建Project`。填写以下信息

|     参数     |     填写示例     |
|:------------:|:----------------:|
| Project名称  |      hyperf      |
|     注释     | 用于日志系统演示 |
|   所属区域   |  华东1（杭州）   |
|   开通服务   |     详细日志     |
| 日志存储位置 |   当前Project    |

### 创建 Logstore

除以下参数，按需填写，其他都使用默认即可

|     参数     |    填写示例     |
|:------------:|:---------------:|
| Logstore名称 | hyperf-demo-api |
|   永久保存   |      false      |
| 数据保存时间 |       60        |

### 接入数据

1. 选择 Docker文件

2. 创建机器组

如果已经创建国机器组，可以跳过这一步

|      参数      |    填写示例    |
|:--------------:|:--------------:|
|   机器组名称   |     Hyperf     |
|   机器组标识   | 用户自定义标识 |
| 用户自定义标识 |     Hyperf     |

3. 配置机器组

应用刚刚创建的机器组

4. 配置 Logtail

Label白名单 这里可以按需填写，以下按照项目名字来配置，而项目名会在Docker容器运行时设置。

|      参数      |                     填写示例                      |
|:--------------:|:-------------------------------------------------:|
|    配置名称    |                  hyperf-demo-api                  |
|    日志路径    |            /opt/www/runtime/logs *.log            |
|  Label白名单   |             app.name hyperf-demo-api              |
|      模式      |                   完整正则模式                    |
|    单行模式    |                       false                       |
|    日志样例    |     [2019-03-07 11:58:57] hyperf.WARNING: xxx     |
| 首行正则表达式 |         \[\d+-\d+-\d+\s\d+:\d+:\d+\]\s.*          |
|    提取字段    |                       true                        |
|   正则表达式   | \[(\d+-\d+-\d+\s\d+:\d+:\d+)\]\s(\w+)\.(\w+):(.*) |
|  日志抽取内容  |              time name level content              |

5. 查询分析配置

字段索引属性

| 字段名称 | 类型 |  别名   | 中文分词 | 开启统计 |
|:--------:|:----:|:-------:|:--------:|:--------:|
|   name   | text |  name   |  false   |   true   |
|  level   | text |  level  |  false   |   true   |
|   time   | text |  time   |  false   |  false   |
| content  | text | content |   true   |  false   |

### 运行镜像

运行镜像时，只需要设置 Container labels 即可。

|   name   |      value      |
|:--------:|:---------------:|
| app.name | hyperf-demo-api |

