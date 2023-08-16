# Nginx reverse proxy

[Nginx](http://nginx.org/) is a high-performance `HTTP` and reverse proxy server, the code is completely implemented with `C`, based on its high performance and many advantages, we can set it as The front-end server of `hyperf`, which implements load balancing or HTTPS front-end server, etc.

## Configure HTTP proxy

```nginx
# At least one Hyperf node is required, multiple configuration lines
upstream hyperf {
    # IP and port of Hyperf HTTP Server
    server 127.0.0.1:9501;
    server 127.0.0.1:9502;
}

server {
    # listening port
    listen 80; 
    # Bound domain name, fill in your domain name
    server_name proxy.hyperf.io;

    location / {
        # Forward the client's Host and IP information to the corresponding node
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # Forward cookies, set SameSite
        proxy_cookie_path / "/; secure; HttpOnly; SameSite=strict";
        
        # Execute proxy access to real server
        proxy_pass http://hyperf;
    }
}
```

## Configure Websocket proxy

```nginx
# At least one Hyperf node is required, multiple configuration lines
upstream hyperf_websocket {
    # Set the load balancing mode to IP Hash algorithm mode, so that each request from different clients will interact with the same node
    ip_hash;
    # IP and port of Hyperf WebSocket Server
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
    
        # The connection between the client and the server is automatically disconnected after 60s of no interaction, please set according to the actual business scenario
        proxy_read_timeout 60s ;
        
        # Execute proxy access to real server
        proxy_pass http://hyperf_websocket;
    }
}
```
