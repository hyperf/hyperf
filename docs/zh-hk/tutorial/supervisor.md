# Supervisor 部署

[Supervisor](http://www.supervisord.org/) 是 `Linux/Unix` 系統下的一個進程管理工具。可以很方便的監聽、啓動、停止和重啓一個或多個進程。通過 [Supervisor](http://www.supervisord.org/) 管理的進程，當進程意外被 `Kill` 時，[Supervisor](http://www.supervisord.org/) 會自動將它重啓，可以很方便地做到進程自動恢復的目的，而無需自己編寫 `shell` 腳本來管理進程。

## 安裝 Supervisor

這裏僅舉例 `CentOS` 系統下的安裝方式：

```bash
# 安裝 epel 源，如果此前安裝過，此步驟跳過
yum install -y epel-release
yum install -y supervisor  
```

## 創建一個配置文件

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

編輯新複製出來的配置文件 `/etc/supervisord.d/supervisord.conf`，並在文件結尾處添加以下內容後保存文件：

```ini
# 新建一個應用並設置一個名稱，這裏設置為 hyperf
[program:hyperf]
# 設置命令在指定的目錄內執行
directory=/var/www/hyperf/
# 這裏為您要管理的項目的啓動命令
command=php ./bin/hyperf.php start
# 以哪個用户來運行該進程
user=root
# supervisor 啓動時自動該應用
autostart=true
# 進程退出後自動重啓進程
autorestart=true
# 進程持續運行多久才認為是啓動成功
startsecs=1
# 重試次數
startretries=3
# stderr 日誌輸出位置
stderr_logfile=/var/www/hyperf/runtime/stderr.log
# stdout 日誌輸出位置
stdout_logfile=/var/www/hyperf/runtime/stdout.log
```

!> 建議同時增大配置文件中的 `minfds` 配置項，默認為 `1024`。同時也應該修改系統的 [ulimit](https://wiki.swoole.com/#/other/sysctl?id=ulimit-%e8%ae%be%e7%bd%ae)，防止出現 `Failed to open stream: Too many open files` 的問題。

## 啓動 Supervisor

運行下面的命令基於配置文件啓動 Supervisor 程序：

```bash
supervisord -c /etc/supervisord.d/supervisord.conf
```

## 使用 supervisorctl 管理項目

```bash
# 啓動 hyperf 應用
supervisorctl start hyperf
# 重啓 hyperf 應用
supervisorctl restart hyperf
# 停止 hyperf 應用
supervisorctl stop hyperf  
# 查看所有被管理項目運行狀態
supervisorctl status
# 重新加載配置文件
supervisorctl update
# 重新啓動所有程序
supervisorctl reload
```
