<?php

require __DIR__ .'/../../vendor/autoload.php';

(function(){
    $config = require __DIR__ .'/../config/hyperf.php';
    $main = new \Phar\Main($config);
    $main->run();
})();