<?php


namespace Hyperf\ConfigApollo;


class IntervalFetchClient extends AbstractClient
{

    public function fetch(array $namespaces, array $callbacks = []): void
    {
        while (true) {
            $this->pull($namespaces, $callbacks);
            sleep($this->config->get('apollo.interval', 5));
        }
    }
}