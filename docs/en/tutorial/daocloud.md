# DaoCloud Devops build tutorial

As an individual developer, the cost of using self-built `Gitlab` and `Docker Swarm cluster` is obviously unacceptable. Here is a `Devops` service `DaoCloud`.

The reason for the recommendation is simple, because it is free and works well.

[DaoCloud](https://dashboard.daocloud.io)

## how to use

You only need to pay attention to the three pages of `project`, `application` and `cluster management`.

### Create project
First we need to create a new project in `projects`. DaoCloud supports a variety of mirror repositories, which can be selected as needed.

Here I use the [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) repository as an example to configure. When the creation is successful, there will be a corresponding url under the `WebHooks` corresponding to the `Github repository`.

Next, let's modify the `Dockerfile` in the repository and add `&& apk add wget \` under `apk add`. The specific reason here is not very clear, if you do not update `wget`, there will be problems when using it. But there is no problem with self-built Gitlab CI.

When the code is submitted, `DaoCloud` will perform the corresponding packaging operation.

### Create cluster

Then we go to `cluster management`, create a `cluster`, and add `hosts`.

I won't go into details here, just follow the steps above.


### Create application

Click Apply -> Create Application -> Select the project just now -> Deploy

According to the instructions, the host port user can choose an unused port, because `DaoCloud` does not have the `Config` function of `Swarm`, so we actively map `.env` to the container.

Add `Volume`, container directory `/opt/www/.env`, host directory Use the address where you store the `.env` file, whether it is writable or not.

Then click Deploy Now.

### test

Go to the host to access the port number just now, and you can see the welcome interface data of `Hyperf`.

```
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```

