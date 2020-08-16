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
namespace Hyperf\Nsq\Api;

abstract class Client
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
}
