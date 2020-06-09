# DaoCloud Devops 搭建

作為個人開發者，使用自建 `Gitlab` 和 `Docker Swarm 叢集` 顯然成本是無法接受的。這裡介紹一個 `Devops` 服務 `DaoCloud`。

推薦理由很簡單，因為它免費，而且還能正常使用。

[DaoCloud](https://dashboard.daocloud.io)

## 如何使用

大家只需要關注 `專案`，`應用` 和 `叢集管理` 三個切頁即可。

### 建立專案

首先我們需要在 `專案` 裡新建一個專案。DaoCloud 支援多種映象倉庫，這個可以按需選擇。

這裡我以 [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) 倉庫為例配置。當建立成功後，在對應 `Github 倉庫` 的 `WebHooks` 下面就會有對應的 url。

接下來我們修改一下倉庫裡的 `Dockerfile`，在 `apk add` 下面增加 `&& apk add wget \`。這裡具體原因不是很清楚，如果不更新 `wget`, 使用時就會有問題。但是自建 Gitlab CI 就沒有任何問題。

當提交程式碼後，`DaoCloud` 就會執行對應的打包操作了。

### 建立叢集 

然後我們到 `叢集管理` 中，建立一個 `叢集`，然後新增 `主機`。

這裡就不詳述了，按照上面的步驟一步一步來就行。

### 建立應用

點選 應用 -> 建立應用 -> 選擇剛剛的專案 -> 部署

按照指示操作，主機埠使用者可以自主選擇一個未使用的埠，因為 `DaoCloud` 沒有 `Swarm` 的 `Config` 功能，所以我們主動把 `.env` 對映到 容器裡。

新增 `Volume`，容器目錄 `/opt/www/.env`，主機目錄 使用你存放 `.env` 檔案的地址，是否可寫 為不可寫。

然後點選 立即部署。

### 測試

到宿主機裡訪問剛剛的埠號，就可以看到 `Hyperf` 的歡迎介面資料了。

```
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```

