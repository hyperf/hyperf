# Supervisor 部署

[Supervisor](http://www.supervisord.org/) 是 `Linux/Unix` 系統下的一個程序管理工具。可以很方便的監聽、啟動、停止和重啟一個或多個程序。透過 [Supervisor](http://www.supervisord.org/) 管理的程序，當程序意外被 `Kill` 時，[Supervisor](http://www.supervisord.org/) 會自動將它重啟，可以很方便地做到程序自動恢復的目的，而無需自己編寫 `shell` 指令碼來管理程序。

## 安裝 Supervisor

這裡僅舉例 `CentOS` 系統下的安裝方式：

```bash
# 安裝 epel 源，如果此前安裝過，此步驟跳過
yum install -y epel-release
yum install -y supervisor  
```

## 建立一個配置檔案

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

編輯新複製出來的配置檔案 `/etc/supervisord.d/supervisord.conf`，並在檔案結尾處新增以下內容後儲存檔案：

```ini
# 新建一個應用並設定一個名稱，這裡設定為 hyperf
[program:hyperf]
# 設定命令在指定的目錄內執行
directory=/var/www/hyperf/
# 這裡為您要管理的專案的啟動命令
command=php ./bin/hyperf.php start
# 以哪個使用者來執行該程序
user=root
# supervisor 啟動時自動該應用
autostart=true
# 程序退出後自動重啟程序
autorestart=true
# 程序持續執行多久才認為是啟動成功
startsecs=1
# 重試次數
startretries=3
# stderr 日誌輸出位置
stderr_logfile=/var/www/hyperf/runtime/stderr.log
# stdout 日誌輸出位置
stdout_logfile=/var/www/hyperf/runtime/stdout.log
```

!> 建議同時增大配置檔案中的 `minfds` 配置項，預設為 `1024`。同時也應該修改系統的 [ulimit](https://wiki.swoole.com/#/other/sysctl?id=ulimit-%e8%ae%be%e7%bd%ae)，防止出現 `Failed to open stream: Too many open files` 的問題。

## 啟動 Supervisor

執行下面的命令基於配置檔案啟動 Supervisor 程式：

```bash
supervisord -c /etc/supervisord.d/supervisord.conf
```

## 使用 supervisorctl 管理專案

```bash
# 啟動 hyperf 應用
supervisorctl start hyperf
# 重啟 hyperf 應用
supervisorctl restart hyperf
# 停止 hyperf 應用
supervisorctl stop hyperf  
# 檢視所有被管理專案執行狀態
supervisorctl status
# 重新載入配置檔案
supervisorctl update
# 重新啟動所有程式
supervisorctl reload
```
