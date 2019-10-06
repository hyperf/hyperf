<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace Hyperf\Etcd\V3;

use Etcd\Client;
use GuzzleHttp\Client as HttpClient;

class EtcdClient extends Client
{
    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;
    }
}
