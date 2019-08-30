# Nginx reverse proxy

[Nginx](http://nginx.org/) is a high-performance `HTTP` and reverse proxy server. The code is implemented entirely in `C`. Based on its high performance and many advantages, we can set it to The front server of `hyperf` implements load balancing or HTTPS front-end servers.

## Configuring the Http proxy

```nginx
# Need at least one Hyperf node, multiple configurations with multiple rows
upstream hyperf {
    # Hyperf HTTP Server IP and port
    server 127.0.0.1:9501;
    server 127.0.0.1:9502;
}

server {
    # Listening port
    listen 80; 
    # Bind domain name, fill in your domain name
    server_name proxy.hyperf.io;

    location / {
        # Forward the client's Host and IP information to the corresponding node
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # Execute proxy access to real server
        proxy_pass http://hyperf;
    }
}
```

## Configuring the Websocket proxy

```nginx
# Need at least one Hyperf node, multiple configurations with multiple rows
upstream hyperf_websocket {
    # Set the load balancing mode to the IP Hash algorithm mode, so that different clients interact with the same node every request.
    ip_hash;
    # Hyperf WebSocket Server IP and port
    server 127.0.0.1:9503;
    server 127.0.0.1:9504;
}

server {
    listen 80;
    server_name websocket.hyperf.io;
    
    location / {
        # WebSocket Header
        proxy_http_version 1.1;
        proxy_set_header Upgrade websocket;
        proxy_set_header Connection "Upgrade";
        
        # Forward the client's Host and IP information to the corresponding node
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
    
        # The client does not interact with the server. After 60s, the connection is automatically disconnected. Set it according to the actual business scenario.
        proxy_read_timeout 60s ;
        
        # Execute proxy access to real server
        proxy_pass http://hyperf_websocket;
    }
}
```
