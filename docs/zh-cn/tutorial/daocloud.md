# DaoCloud Devops 搭建

作为个人开发者，使用自建 `Gitlab` 和 `Docker Swarm 集群` 显然成本是无法接受的。这里介绍一个 `Devops` 服务 `DaoCloud`。

推荐理由很简单，因为它免费，而且还能正常使用。

[DaoCloud](https://dashboard.daocloud.io)

## 如何使用

大家只需要关注 `项目`，`应用` 和 `集群管理` 三个切页即可。

### 创建项目

首先我们需要在 `项目` 里新建一个项目。DaoCloud 支持多种镜像仓库，这个可以按需选择。

这里我以 [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) 仓库为例配置。当创建成功后，在对应 `Github 仓库` 的 `WebHooks` 下面就会有对应的 url。

接下来我们修改一下仓库里的 `Dockerfile`，在 `apk add` 下面增加 `&& apk add wget \`。这里具体原因不是很清楚，如果不更新 `wget`, 使用时就会有问题。但是自建 Gitlab CI 就没有任何问题。

每次提交代码后，`DaoCloud` 都会对你创建的项目执行对应的打包操作。

### 创建集群 

然后我们到 `集群管理` 中，创建一个 `集群`，然后添加 `主机`。

这里就不详述了，按照上面的步骤一步一步来就行。

### 创建应用

点击 应用 -> 创建应用 -> 选择刚刚的项目（需至少提交过一次代码，`DaoCloud`打包生成了镜像才能部署） -> 部署

按照指示操作，主机端口用户可以自主选择一个未使用的端口，因为 `DaoCloud` 没有 `Swarm` 的 `Config` 功能，所以我们主动把 `.env` 映射到 容器里。

添加 `Volume`，容器目录 `/opt/www/.env`，主机目录 使用你存放 `.env` 文件的地址，是否可写 为不可写。

然后点击 立即部署。

### 测试

到宿主机里访问刚刚的端口号，就可以看到 `Hyperf` 的欢迎接口数据了。

```
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```

