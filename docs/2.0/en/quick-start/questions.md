# FAQ

## Swoole Short Name has not been disabled

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

This may be because you set it up as follows

```
// These are all wrong, pay attention to `word case` and `quotes`
swoole.use_shortname = 'off'
swoole.use_shortname = off
swoole.use_shortname = Off
// The following is correct
swoole.use_shortname = 'Off'
```

> Note that this configuration MUST be configured in php.ini and CANNOT be overridden by the ini_set() function.

## Proxy class cache

Once the proxy class cache is generated, it will not be overwritten again. So when you modify the file that has generated the proxy class, you need to manually clean it up.

The proxy class location in:
```
runtime/container/proxy/
```

Re-genenrate command
```
vendor/bin/init-proxy.sh
```

So the command to run unit test can use the following instead
```
vendor/bin/init-proxy.sh && composer test
```

Similarly, the command to start the server can also use the following instead
```
vendor/bin/init-proxy.sh && php bin/hyperf.php start
```

## Docker build failure

Display `wget: error getting response: Connection reset by peer`

Modify the `Dockerfile`, reinstall `wget`, add the following code:

```
&& apk add wget \
```