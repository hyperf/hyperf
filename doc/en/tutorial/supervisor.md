# Supervisor deployment

[Supervisor](http://www.supervisord.org/) is a process management tool under the `Linux/Unix` system. It is convenient to monitor, start, stop, and restart one or more processes. Processes managed by [Supervisor](http://www.supervisord.org/), when the process is accidentally `Kill`, [Supervisor](http://www.supervisord.org/) will automatically restart it. It's easy to automate the process without having to write a `shell` script to manage the process.

## Install Supervisor

Here is only an example of how to install the `CentOS` system:

```bash
# Install the epel source, skip this step if it was previously installed
yum install -y epel-release
yum install -y supervisor  
```

## Create a configuration file

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

Edit the newly copied configuration file `/etc/supervisord.d/supervisord.conf` and save the file after adding the following at the end of the file:

```ini
# Create a new app and set a name, set to hyperf here
[program:hyperf]
# Set the command to execute in the specified directory
directory=/var/www/hyperf/
# Here is the start command for the project you want to manage
command=php ./bin/hyperf.php start
# Which user is running the process
user=root
# Automatically launch the application when the supervisor starts
autostart=true
# Automatically restart the process after the process exits
autorestart=true
# How long does the process continue to run before it is considered successful
startsecs=1
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

## Manage projects with supervisorctl

```bash
# Start the hyperf app
supervisorctl start hyperf
# Restart the hyperf app
supervisorctl restart hyperf
# Stop hyperf application
supervisorctl stop hyperf  
# View all managed project running status
supervisorctl status
# Reload the configuration file
supervisorctl update
# Restart all programs
supervisorctl reload
```
