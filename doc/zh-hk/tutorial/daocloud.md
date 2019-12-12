# DaoCloud Devops 搭建

作為個人開發者，使用自建 `Gitlab` 和 `Docker Swarm 集羣` 顯然成本是無法接受的。這裏介紹一個 `Devops` 服務 `DaoCloud`。

推薦理由很簡單，因為它免費，而且還能正常使用。

[DaoCloud](https://dashboard.daocloud.io)

## 如何使用

大家只需要關注 `項目`，`應用` 和 `集羣管理` 三個切頁即可。

### 創建項目

首先我們需要在 `項目` 裏新建一個項目。DaoCloud 支持多種鏡像倉庫，這個可以按需選擇。

這裏我以 [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) 倉庫為例配置。當創建成功後，在對應 `Github 倉庫` 的 `WebHooks` 下面就會有對應的 url。

接下來我們修改一下倉庫裏的 `Dockerfile`，在 `apk add` 下面增加 `&& apk add wget \`。這裏具體原因不是很清楚，如果不更新 `wget`, 使用時就會有問題。但是自建 Gitlab CI 就沒有任何問題。

當提交代碼後，`DaoCloud` 就會執行對應的打包操作了。

### 創建集羣 

然後我們到 `集羣管理` 中，創建一個 `集羣`，然後添加 `主機`。

這裏就不詳述了，按照上面的步驟一步一步來就行。

### 創建應用

點擊 應用 -> 創建應用 -> 選擇剛剛的項目 -> 部署

按照指示操作，主機端口用户可以自主選擇一個未使用的端口，因為 `DaoCloud` 沒有 `Swarm` 的 `Config` 功能，所以我們主動把 `.env` 映射到 容器裏。

添加 `Volume`，容器目錄 `/opt/www/.env`，主機目錄 使用你存放 `.env` 文件的地址，是否可寫 為不可寫。

然後點擊 立即部署。

### 測試

到宿主機裏訪問剛剛的端口號，就可以看到 `Hyperf` 的歡迎接口數據了。

```
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```

