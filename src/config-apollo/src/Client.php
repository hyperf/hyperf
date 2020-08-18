<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\ConfigApollo;


class Client extends AbstractClient
{

    public function fetch(array $namespaces, array $callbacks = []): void
    {
        while (true) {
            $this->pull($namespaces, $callbacks);
            sleep($this->config->get('apollo.interval', 5));
        }
    }
}