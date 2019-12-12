# Nginx 反向代理

[Nginx](http://nginx.org/) 是一個高性能的 `HTTP` 和反向代理服務器，代碼完全用 `C` 實現，基於它的高性能以及諸多優點，我們可以把它設置為 `hyperf` 的前置服務器，實現負載均衡或 HTTPS 前置服務器等。

## 配置 Http 代理

```nginx
# 至少需要一個 Hyperf 節點，多個配置多行
upstream hyperf {
    # Hyperf HTTP Server 的 IP 及 端口
    server 127.0.0.1:9501;
    server 127.0.0.1:9502;
}

server {
    # 監聽端口
    listen 80; 
    # 綁定的域名，填寫您的域名
    server_name proxy.hyperf.io;

    location / {
        # 將客户端的 Host 和 IP 信息一併轉發到對應節點  
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # 執行代理訪問真實服務器
        proxy_pass http://hyperf;
    }
}
```

## 配置 Websocket 代理

```nginx
# 至少需要一個 Hyperf 節點，多個配置多行
upstream hyperf_websocket {
    # 設置負載均衡模式為 IP Hash 算法模式，這樣不同的客户端每次請求都會與同一節點進行交互
    ip_hash;
    # Hyperf WebSocket Server 的 IP 及 端口
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
        
        # 將客户端的 Host 和 IP 信息一併轉發到對應節點  
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
    
        # 客户端與服務端無交互 60s 後自動斷開連接，請根據實際業務場景設置
        proxy_read_timeout 60s ;
        
        # 執行代理訪問真實服務器
        proxy_pass http://hyperf_websocket;
    }
}
```
