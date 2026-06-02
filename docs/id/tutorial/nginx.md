# Nginx Reverse Proxy

[Nginx](http://nginx.org/) adalah server HTTP dan reverse proxy berperforma tinggi, yang kodenya ditulis murni dalam C. Berkat performa dan keunggulannya, kita bisa menggunakannya sebagai front-end server untuk Hyperf, baik untuk keperluan load balancing maupun sebagai front-end server HTTPS.

## Konfigurasi Proxy HTTP

```nginx
# Setidaknya diperlukan satu node Hyperf; beberapa baris untuk beberapa node
upstream hyperf {
    # IP dan Port dari Hyperf HTTP Server
    server 127.0.0.1:9501;
    server 127.0.0.1:9502;
}

server {
    # Port yang di-listen
    listen 80; 
    # Nama domain yang terikat pada server, isi dengan domain Anda
    server_name proxy.hyperf.io;

    location / {
        # Teruskan informasi Host dan IP klien ke node yang sesuai
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        
        # Teruskan Cookie, atur SameSite
        proxy_cookie_path / "/; secure; HttpOnly; SameSite=strict";
        
        # Jalankan proxy akses ke server sebenarnya
        proxy_pass http://hyperf;
    }
}
```

## Konfigurasi Proxy WebSocket

```nginx
# Setidaknya diperlukan satu node Hyperf; beberapa baris untuk beberapa node
upstream hyperf_websocket {
    # Atur mode load balancing ke algoritma IP Hash agar klien yang sama selalu terhubung ke node yang sama
    ip_hash;
    # IP dan Port dari Hyperf WebSocket Server
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
        
        # Teruskan informasi Host dan IP klien ke node yang sesuai
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
    
        # Putuskan koneksi otomatis setelah 60 detik tanpa interaksi, sesuaikan dengan kebutuhan bisnis
        proxy_read_timeout 60s ;
        
        # Jalankan proxy akses ke server sebenarnya
        proxy_pass http://hyperf_websocket;
    }
}
```
