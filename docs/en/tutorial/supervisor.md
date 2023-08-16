# Supervisor deploy application

[Supervisor](http://www.supervisord.org/) is a process management tool under `Linux/Unix` system. One or more processes can be easily monitored, started, stopped and restarted. Processes managed by [Supervisor](http://www.supervisord.org/), when the process is accidentally `Kill`, [Supervisor](http://www.supervisord.org/) will automatically restart it, It is very convenient to achieve the purpose of automatic process recovery without having to write a `shell` script to manage the process.

## Installation Supervisor

Here is just an example of the installation method under the `CentOS` system:

```bash
# Install the epel source, if it has been installed before, skip this step
yum install -y epel-release
yum install -y supervisor  
```

## create a configuration file

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

Edit the newly copied configuration file `/etc/supervisord.d/supervisord.conf` and save the file after adding the following at the end of the file:

```ini
# Create a new application and set a name, here is set to hyperf
[program:hyperf]
# Here is the startup command of the project you want to manage, corresponding to the real path of your project
command=php /var/www/hyperf/bin/hyperf.php start
# Which user to run the process as
user=root
# automatically the app when supervisor starts
autostart=true
# Automatically restart the process after the process exits
autorestart=true
# retry interval in seconds
startsecs=5
# number of retries
startretries=3
# stderr log output location
stderr_logfile=/var/www/hyperf/runtime/stderr.log
# stdout log output location
stdout_logfile=/var/www/hyperf/runtime/stdout.log
```

## Start Supervisor

Run the following command to start the Supervisor program based on the configuration file:

```bash
supervisord -c /etc/supervisord.d/supervisord.conf
```

## Use `supervisorctl` to manage the application

```bash
# start the hyperf application
supervisorctl start hyperf
# restart hyperf application
supervisorctl restart hyperf
# stop hyperf application
supervisorctl stop hyperf
# View the running status of all managed projects
supervisorctl status
```
