# 生命週期

## 框架生命週期

Hyperf 是運行於 [Swoole](http://github.com/swoole/swoole-src) 之上的，想要理解透徹 Hyperf 的生命週期，那麼理解 [Swoole](http://github.com/swoole/swoole-src) 的生命週期也至關重要。   
Hyperf 的命令管理預設由 [symfony/console](https://github.com/symfony/console) 提供支援*(如果您希望更換該元件您也可以透過改變 skeleton 的入口檔案更換成您希望使用的元件)*，在執行 `php bin/hyperf.php start` 後，將由 `Hyperf\Server\Command\StartServer` 命令類接管，並根據配置檔案 `config/autoload/server.php` 內定義的 `Server` 逐個啟動。   
關於依賴注入容器的初始化工作，我們並沒有由元件來實現，因為一旦交由元件來實現，這個耦合就會非常的明顯，所以在預設的情況下，是由入口檔案來載入 `config/container.php` 來實現的。

## 請求與協程生命週期

Swoole 在處理每個連線時，會預設建立一個協程去處理，主要體現在 `onRequest`、`onReceive`、`onConnect` 事件，所以可以理解為每個請求都是一個協程，由於建立協程也是個常規操作，所以一個請求協程裡面可能會包含很多個協程，同一個程序內協程之間是記憶體共享的，但排程順序是非順序的，且協程間本質上是相互獨立的沒有父子關係，所以對每個協程的狀態處理都需要透過 [協程上下文](zh-tw/coroutine.md#協程上下文) 來管理。   

