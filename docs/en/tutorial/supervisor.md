# Supervisor Deployment

[Supervisor](http://www.supervisord.org/) is a process management tool for `Linux/Unix` systems. It can easily monitor, start, stop, and restart one or more processes. Processes managed by [Supervisor](http://www.supervisord.org/) will be automatically restarted by [Supervisor](http://www.supervisord.org/) when they are accidentally `Killed`, making it easy to achieve automatic process recovery without having to write `shell` scripts yourself to manage processes.

## Installing Supervisor

The following is an example of the installation method on `CentOS`:

```bash
# Install epel source; skip this step if installed previously
yum install -y epel-release
yum install -y supervisor  
```

## Creating a Configuration File

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

Edit the newly copied configuration file `/etc/supervisord.d/supervisord.conf`, and add the following content to the end of the file before saving:

```ini
# Create a new application and set a name, here set to hyperf
[program:hyperf]
# Set the command to execute in the specified directory
directory=/var/www/hyperf/
# This is the startup command for the project you want to manage
command=php ./bin/hyperf.php start
# Which user to run this process as
user=root
# Automatically start this application when supervisor starts
autostart=true
# Automatically restart the process after it exits
autorestart=true
# How long the process must run to be considered successfully started
startsecs=1
# Number of retry attempts
startretries=3
# stderr log output location
stderr_logfile=/var/www/hyperf/runtime/stderr.log
# stdout log output location
stdout_logfile=/var/www/hyperf/runtime/stdout.log
```

!> It is recommended to increase the `minfds` configuration item in the configuration file, which defaults to `1024`. You should also modify the system's [ulimit](https://wiki.swoole.com/#/other/sysctl?id=ulimit-settings) to prevent the `Failed to open stream: Too many open files` problem.

## Starting Supervisor

Run the following command to start the Supervisor program based on the configuration file:

```bash
supervisord -c /etc/supervisord.d/supervisord.conf
```

## Using supervisorctl to Manage Projects

```bash
# Start hyperf application
supervisorctl start hyperf
# Restart hyperf application
supervisorctl restart hyperf
# Stop hyperf application
supervisorctl stop hyperf  
# View the running status of all managed projects
supervisorctl status
# Reload configuration file
supervisorctl update
# Restart all programs
supervisorctl reload
```
