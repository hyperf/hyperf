# Nginx 代理Http和Websocket

[Nginx(engine x)](http://nginx.org/) 是一个高性能的HTTP和反向代理WEB服务器,代码完全用C语言从头写成。基于它的高性能以及诸多优点，我们把它设置为hyperf的前置服务器

## 配置为Http Request 代理服务器

```nginx

#注意，这段放置在server块之外,至少需要一个服务器ip
upstream  hyperf_backend_server_list {
        server  127.0.0.1:9501  ;
        server  127.0.0.1:9502  ;
}
server
    {
        #监听端口
        listen 20181  ; 
         #  站点域名，没有的话，写项目名称即可
        server_name hyperf_proxy.com ; 
        
        # 如果对跨域允许的ip管控不是很严格，可以设置如下
          add_header Access-Control-Allow-Origin *;
          add_header Access-Control-Allow-Headers 'Authorization, User-Agent, Keep-Alive, Content-Type, X-Requested-With';
          add_header Access-Control-Allow-Methods OPTIONS, GET, POST, DELETE, PUT, PATCH
            
    if ($request_method = 'OPTIONS') {
        
        # 针对浏览器第一次OPTIONS请求响应状态码：200，消息：hello options（可随意填写，避免中文）
        return 200 "hello options";
        
    }

 location   / {
     
       #将客户端的ip和头域信息一并转发到后端服务器  
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # 最后，执行代理访问真实服务器
        proxy_pass http://hyperf_backend_server_list   ;

    }
```

## 配置Websocket代理服务器

```nginx
#注意，这段放置在server块之外,至少需要一个服务器ip
upstream  hyperf_backend_websocket_list {
        # 设置负载均衡模式为ip算法模式，这样不同的客户端每次请求都会与第一次建立对话的后端服务器进行交互
        ip_hash;
        server  127.0.0.1:9503  ;
        server  127.0.0.1:9504  ;
}

server
    {
        listen 20182 ;
        server_name websocket.la5.com ;
location    /   {
    # websocket 必须的头参数
    proxy_http_version 1.1;
    proxy_set_header Upgrade websocket;
    proxy_set_header Connection "Upgrade";
    
    #将客户端的ip和头域信息一并转发到后端服务器
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Host $http_host;
    
    # 客户端与服务端无任何交互，60s后自动断开连接，根据实际业务场景设置
    proxy_read_timeout  60s ;
    
    # 最后，执行代理访问真实服务器
    proxy_pass http://hyperf_backend_websocket_list;
  }
```