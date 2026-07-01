# Setting up DaoCloud DevOps

As an individual developer, the cost of self-building `Gitlab` and a `Docker Swarm Cluster` is clearly unacceptable. Here, we introduce a `DevOps` service: `DaoCloud`.

The reason for recommending it is simple: it is free, and it works normally.

[DaoCloud](https://dashboard.daocloud.io)

## How to Use

You only need to focus on the three tabs: `Projects`, `Applications`, and `Cluster Management`.

### Create a Project

First, we need to create a new project in `Projects`. DaoCloud supports various image registries, which can be selected according to your needs.

Here, I will use the [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) repository as an example for configuration. When creation is successful, a corresponding URL will appear under the `WebHooks` of the corresponding `Github repository`.

Next, we modify the `Dockerfile` in the repository and add `&& apk add wget \` under `apk add`. The specific reason for this is not very clear, but if `wget` is not updated, there will be problems during usage. However, self-built Gitlab CI has no such problems.

After each code submission, `DaoCloud` will execute the corresponding packaging operation for the project you created.

### Create a Cluster

Then we go to `Cluster Management`, create a `Cluster`, and add a `Host`.

I will not elaborate here; just follow the steps one by one as instructed above.

### Create an Application

Click Applications -> Create Application -> Select the project you just created (must have submitted code at least once, and `DaoCloud` must have generated an image to be deployed) -> Deploy.

Follow the instructions. You can choose an unused port for the host port by yourself. Because `DaoCloud` does not have the `Config` function of `Swarm`, we manually map the `.env` file to the container.

Add `Volume`: container directory `/opt/www/.env`, host directory uses the address where you store the `.env` file, and set it to non-writable.

Then click "Deploy Now".

### Testing

Access the port just used in the host machine, and you will see the welcome interface data of `Hyperf`.

```bash
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```
