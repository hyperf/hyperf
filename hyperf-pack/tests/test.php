<?php

$shell = "php -d phar.readonly=0 \n
bin/bootstrap.php build \n
-d /hyperf-skeleton/hyperf/ \n
-o /hyperf-skeleton/hyperf/bin/hyperf.phar \n
-b bin/hyperf.php  
";