# Proxy reverso com Nginx

[Nginx](http://nginx.org/) é um servidor `HTTP` e de proxy reverso de alta performance, cujo código é totalmente implementado em `C`. Com base na alta performance e em várias vantagens, podemos usá-lo como servidor de front-end do `hyperf`, implementando balanceamento de carga, servidor de front-end HTTPS etc.

## Configurar proxy HTTP

```nginx
# É necessário ao menos um nó Hyperf; múltiplas linhas de configuração
upstream hyperf {
    # IP e porta do servidor HTTP do Hyperf
    server 127.0.0.1:9501;
    server 127.0.0.1:9502;
}

server {
    # porta de escuta
    listen 80; 
    # Domínio vinculado; preencha com seu domínio
    server_name proxy.hyperf.io;

    location / {
        # Encaminha Host e IP do cliente para o nó correspondente
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # Encaminha cookies; define SameSite
        proxy_cookie_path / "/; secure; HttpOnly; SameSite=strict";
        
        # Executa acesso via proxy ao servidor real
        proxy_pass http://hyperf;
    }
}
```

## Configurar proxy WebSocket

```nginx
# É necessário ao menos um nó Hyperf; múltiplas linhas de configuração
upstream hyperf_websocket {
    # Define o balanceamento como IP Hash, para que cada cliente interaja com o mesmo nó
    ip_hash;
    # IP e porta do servidor WebSocket do Hyperf
    server 127.0.0.1:9503;
    server 127.0.0.1:9504;
}

server {
    listen 80;
    server_name websocket.hyperf.io;
    
    location / {
        # Headers de WebSocket
        proxy_http_version 1.1;
        proxy_set_header Upgrade websocket;
        proxy_set_header Connection "Upgrade";
        
        # Encaminha Host e IP do cliente para o nó correspondente  
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
    
        # A conexão entre cliente e servidor é desconectada automaticamente após 60s sem interação; ajuste conforme o cenário
        proxy_read_timeout 60s ;
        
        # Executa acesso via proxy ao servidor real
        proxy_pass http://hyperf_websocket;
    }
}
```
