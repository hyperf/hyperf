# Nginx Reverse Proxy

[Nginx](http://nginx.org/) is a high-performance HTTP and reverse proxy server. Its code is fully implemented in C. Due to its high performance and numerous advantages, we can set it up as a front-end server for Hyperf to implement load balancing or act as a front-end server for HTTPS, among other things.

## Configure HTTP Proxy

```nginx
# At least one Hyperf node is required; multiple lines for multiple nodes
upstream hyperf {
    # IP and Port of Hyperf HTTP Server
    server 127.0.0.1:9501;
    server 127.0.0.1:9502;
}

server {
    # Listening port
    listen 80; 
    # The domain name bound to the server, fill in your domain
    server_name proxy.hyperf.io;

    location / {
        # Forward client's Host and IP information to the corresponding node
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # Forward Cookie, set SameSite
        proxy_cookie_path / "/; secure; HttpOnly; SameSite=strict";
        
        # Execute proxy access to the real server
        proxy_pass http://hyperf;
    }
}
```

## Configure WebSocket Proxy

```nginx
# At least one Hyperf node is required; multiple lines for multiple nodes
upstream hyperf_websocket {
    # Set load balancing mode to IP Hash algorithm mode, so that different clients will interact with the same node for each request
    ip_hash;
    # IP and Port of Hyperf WebSocket Server
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
        
        # Forward client's Host and IP information to the corresponding node
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
    
        # Automatically disconnect after 60s of no interaction between client and server, please set according to actual business scenarios
        proxy_read_timeout 60s ;
        
        # Execute proxy access to the real server
        proxy_pass http://hyperf_websocket;
    }
}
```
