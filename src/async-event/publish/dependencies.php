<?php

declare(strict_types=1);
return [
    \Psr\EventDispatcher\EventDispatcherInterface::class => \Hyperf\AsyncEvent\AsyncEventDispatcherFactory::class,
];
