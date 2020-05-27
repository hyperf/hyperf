<?php


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