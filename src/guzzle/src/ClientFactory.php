<?php

namespace Hyperf\Guzzle;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class ClientFactory
{

    public function createClient(): Client
    {
        $stack = HandlerStack::create(new CoroutineHandler());
        return new Client(['handler' => $stack]);
    }

}