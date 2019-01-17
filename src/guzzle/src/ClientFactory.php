<?php

namespace Hyperf\Guzzle;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class ClientFactory
{

    public static function createClient(array $options = []): Client
    {
        $stack = HandlerStack::create(new CoroutineHandler());
        return new Client(array_replace(['handler' => $stack], $options));
    }

}