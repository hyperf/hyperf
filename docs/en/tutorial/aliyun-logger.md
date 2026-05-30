# Alibaba Cloud Log Service

When deploying projects in a `Docker Swarm` cluster, collecting logs can be a cumbersome task. However, Alibaba Cloud provides a very convenient `Log Collection System`. This document briefly introduces how to use Alibaba Cloud's log collection.

* [Docker Swarm Cluster Setup](docker-swarm.md)

## Enable Log Service

The first step is to enable `Log Service` on Alibaba Cloud.

[Log Service Documentation](https://help.aliyun.com/product/28958.html)

The following tutorial is a step-by-step guide on how to use the Log Service.

## Install Logtail Container

[Standard Docker Log Collection Process Documentation](https://help.aliyun.com/document_detail/66659.html)

| Parameter | Description |
| :---: | :---: |
| ${your_region_name} | Region ID, e.g., East China 1 region is cn-hangzhou |
| ${your_aliyun_user_id} | User identifier, please replace with your Alibaba Cloud main account user ID. |
| ${your_machine_group_user_defined_id} | Custom identifier for the cluster machine group. Hyperf is used below. |

```
docker run -d -v /:/logtail_host:ro -v /var/run/docker.sock:/var/run/docker.sock \
--env ALIYUN_LOGTAIL_CONFIG=/etc/ilogtail/conf/${your_region_name}/ilogtail_config.json \
--env ALIYUN_LOGTAIL_USER_ID=${your_aliyun_user_id} \
--env ALIYUN_LOGTAIL_USER_DEFINED_ID=${your_machine_group_user_defined_id} \
registry.cn-hangzhou.aliyuncs.com/log-service/logtail
```

## Configure Log Collection

### Create Project

Log in to Alibaba Cloud Log Service, click `Create Project`, and fill in the following information:

| Parameter | Example |
| :---: | :---: |
| Project Name | hyperf |
| Description | Used for log system demonstration |
| Region | East China 1 (Hangzhou) |
| Enable Service | Detailed Logs |
| Log Storage Location | Current Project |

### Create Logstore

Except for the following parameters, fill in as needed, and keep the defaults for others.

| Parameter | Example |
| :---: | :---: |
| Logstore Name | hyperf-demo-api |
| Permanent Storage | false |
| Data Retention Time | 60 |

### Access Data

1. Select Docker File

2. Create Machine Group

If you have already created a machine group, you can skip this step.

| Parameter | Example |
| :---: | :---: |
| Machine Group Name | Hyperf |
| Machine Group Identifier | User-defined identifier |
| User-defined Identifier | Hyperf |

3. Configure Machine Group

Apply the machine group just created.

4. Configure Logtail

`Label` whitelist can be filled in as needed. Configure according to the project name below, and the project name will be set when the Docker container runs.

| Parameter | Example | Example |
| :---: | :---: | :---: |
| Configuration Name | hyperf-demo-api | |
| Log Path | /opt/www/runtime/logs | *.log |
| Label Whitelist | app.name | hyperf-demo-api |
| Mode | Full Regex Mode | |
| Single-line Mode | false | |
| Log Sample | `[2019-03-07 11:58:57] hyperf.WARNING: xxx` | |
| First-line Regex | `\[\d+-\d+-\d+\s\d+:\d+:\d+\]\s.*` | |
| Extract Fields | true | |
| Regex | `\[(\d+-\d+-\d+\s\d+:\d+:\d+)\]\s(\w+)\.(\w+):(.*)` | |
| Log Extraction Content | time name level content | |

5. Query Analysis Configuration

Field Index Attributes

| Field Name | Type | Alias | Chinese Word Segmentation | Enable Statistics |
| :---: | :---: | :---: | :---: | :---: |
| name | text | name | false | true |
| level | text | level | false | true |
| time | text | time | false | false |
| content | text | content | true | false |

### Run Image

When running the image, just set the Container `labels`.

| name | value |
| :---: | :---: |
| app.name | hyperf-demo-api |

For example, the following Dockerfile:

```Dockerfile
# Default Dockerfile

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="hyperf-demo-api"

# Other content omitted
```

## Precautions

- Docker storage driver limitation: Currently, only `overlay` and `overlay2` are supported. For other storage drivers, you need to `mount` the directory where the logs are located to the local machine, and then change it to collect logs under the host's `~/logtail_host/your_path`.
