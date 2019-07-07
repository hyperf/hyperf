# supervisor以进程守护方式部署
supervisor是Linux/Unix系统下的一个进程管理工具。可以很方便的监听、启动、停止、重启一个或多个进程。用supervisor管理的进程，当一个进程意外被杀死，supervisor监听到进程死后，会自动将它重启，很方便的做到进程自动恢复的功能，不再需要自己写shell脚本来控制。
## 安装 supervisor
```
yum install epel-release  #如果此前安装过，此步骤跳过
yum install -y supervisor  
```

## 创建一个配置文件
```
cp   /etc/supervisord.conf     /etc/etc/supervisord.d/supervisord.conf
```

## 编辑刚才新复制的配置文件
```
vim /etc/etc/supervisord.d/supervisord.conf 

```
## 文件结尾添加以下内容，保存
```
[program:hyperf_test1]  # hyperf_test1为项目设置一个名称
command=php  /home/wwwroot/www.hyperf.com/backend/bin/hyperf.php  start  # 启动hyperf项目命令，注意修改为自己的项目路径
user=root
autostart=true
autorestart=true
startsecs = 5
startretries = 3
stderr_logfile=/home/wwwroot/www.hyperf.com/err.log
stdout_logfile=/home/wwwroot/www.hyperf.com/out.log  
```
## 启动(带配置文件)
```
supervisord -c /etc/supervisord.d/supervisord.conf

```

## 使用supervisor管理本项目
```
supervisorctl stop hyperf_test1  
supervisorctl start hyperf_test1
supervisorctl restart hyperf_test1

```
## 查看项目运行状态
```
supervisorctl  status
```