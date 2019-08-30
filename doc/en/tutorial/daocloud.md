# DaoCloud Devops Build

As an individual developer, using self-built `Gitlab` and `Docker Swarm clusters` is obviously unacceptable. Here is a `Devops` service `DaoCloud`.

The reason for the recommendation is simple because it is free and can be used normally.

[DaoCloud](https://dashboard.daocloud.io)

## How to use

You only need to pay attention to `project`, `application` and `cluster management`.

### Create project

First we need to create a new project in `project`. DaoCloud supports multiple mirrored repositories, which can be selected on demand.

Here I use the [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) repository as an example. When the creation is successful, there will be a corresponding url under `WebHooks` corresponding to `Github Repository`.

Next we modify the `Dockerfile` in the repository and add `&& apk add wget \` under `apk add`. The specific reason here is not very clear. If you don't update `wget`, you will have problems when using it. However, there is no problem with self-built Gitlab CI.

When the code is submitted, `DaoCloud` will perform the corresponding packaging operation.

### Create a cluster

Then we go to `Cluster Management`, create a `cluster` and add `host`.

I won't go into details here, follow the steps above step by step.

### Create an app

Click Apply -> Create Application -> Select Project just -> Deploy

According to the instructions, the host port user can choose an unused port, because `DaoCloud` does not have `Swarm` `Config` function, so we actively map `.env` to the container.

Add `Volume`, container directory `/opt/www/.env`, host directory Use the address where you store the `.env` file, whether it can be written as non-writable.

Then click Deploy now.

### Test

Go to the host to access the port number just now, you can see the welcome interface data of `Hyperf`.

```
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```

