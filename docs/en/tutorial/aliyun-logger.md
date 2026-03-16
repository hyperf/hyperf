# Alibaba Cloud Log Service

Collecting logs can be a troublesome problem when deploying a project in a `Docker cluster`, but Alibaba Cloud provides a very useful `log collection system`. This document briefly introduces how to use Alibaba Cloud log collection.

* [Docker Swarm cluster building](zh-cn/tutorial/docker-swarm.md)

## Enable log service

The first step is to activate the `Log Service` on Alibaba Cloud.

[Log Service Documentation](https://help.aliyun.com/product/28958.html)

The following tutorial is a sequential, step-by-step guide on how to use the log service.

## Install the Logtail container

[Standard Docker log collection process document](https://help.aliyun.com/document_detail/66659.html)

| Parameters | Description |
| :-----------------------------------: | :------------ -------------------------------: |
| ${your_region_name} | Region ID For example, the East China 1 region is cn-hangzhou |
| ${your_aliyun_user_id} | User ID, please replace it with your Alibaba Cloud primary account user ID. |
| ${your_machine_group_user_defined_id} | The machine group custom ID of the cluster The following uses Hyperf |

````
docker run -d -v /:/logtail_host:ro -v /var/run/docker.sock:/var/run/docker.sock \
--env ALIYUN_LOGTAIL_CONFIG=/etc/ilogtail/conf/${your_region_name}/ilogtail_config.json \
--env ALIYUN_LOGTAIL_USER_ID=${your_aliyun_user_id} \
--env ALIYUN_LOGTAIL_USER_DEFINED_ID=${your_machine_group_user_defined_id} \
registry.cn-hangzhou.aliyuncs.com/log-service/logtail
````

## Configure log collection

### Create Project

Login to Alibaba Cloud Log Service, click `Create Project`, and fill in the following information

| Parameters | Fill in the example |
| :------------: | :------------------: |
| Project name | hyperf |
| Comments | For log system demonstration |
| Region | East China 1 (Hangzhou) |
| Activate service | Detailed log |
| Log Storage Location | Current Project |

### Create Logstore

Except for the following parameters, fill in as needed, others can use the default

| Parameters | Fill in the example |
| :------------: | :-------------: |
| Logstore name | hyperf-demo-api |
| save permanently | false |
| Data retention time | 60 |

### Access data

1. Select the Docker file

2. Create a machine group

If you have already created a machine group, you can skip this step

| Parameters | Fill in the example |
| :------------: | :------------: |
| Machine Group Name | Hyperf |
| Machine group ID | User-defined ID |
| User Defined Logo | Hyperf |

3. Configure the machine group

Apply the machine group you just created

4. Configure Logtail

`Label` whitelist, here you can fill in as needed, the following is configured according to the project name, and the project name will be set when the Docker container is running.

| Parameters | Fill in the example | Fill in the example |
| :------------: | :-------------------------------- ----------------: | :-------------: |
| Configuration Name | hyperf-demo-api | |
| Log Path | /opt/www/runtime/logs | *.log |
| Label whitelist | app.name | hyperf-demo-api |
| Pattern | Full Regular Pattern | |
| single-line mode | false | |
| Sample log | `[2019-03-07 11:58:57] hyperf.WARNING: xxx` | |
| First line regular expression | `\[\d+-\d+-\d+\s\d+:\d+:\d+\]\s.*` | |
| Extract fields | true | |
| Regular Expression | `\[(\d+-\d+-\d+\s\d+:\d+:\d+)\]\s(\w+)\.(\w+):(.*)` | |
| Log extraction content | time name level content | |

5. Query analysis configuration

field index property

| Field name | Type | Alias ​​| Chinese word segmentation | Open statistics |
| :------: | :---: | :-----: | :------: | :------: |
| name | text | name | false | true |
| level | text | level | false | true |
| time | text | time | false | false |
| content | text | content | true | false |

### Run the image

When running the image, all you need to do is set the Container `labels`.

| name | value |
| :------: | :-------------: |
| app.name | hyperf-demo-api |

For example the following Dockerfile

```Dockerfile
# Default Dockerfile

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="hyperf-demo-api"

#Other content omitted
````

## Precautions

- Docker storage driver limitation: Currently, only `overlay` and `overlay2` are supported. For other storage drivers, you need to `mount` the directory where the logs are located, and then collect the logs from the host `~/logtail_host/your_path` instead.