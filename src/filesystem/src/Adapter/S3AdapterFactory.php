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

namespace Hyperf\Filesystem\Adapter;

use Aws\Handler\GuzzleV6\GuzzleHandler;
use Aws\S3\S3Client;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use Hyperf\Filesystem\Version;
use Hyperf\Guzzle\CoroutineHandler;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

class S3AdapterFactory implements AdapterFactoryInterface
{
    public function make(array $options)
    {
        $handler = new GuzzleHandler(new Client([
            'handler' => HandlerStack::create(new CoroutineHandler()),
        ]));
        $options = array_merge($options, ['http_handler' => $handler]);
        $client = new S3Client($options);

        if (Version::isV2()) {
            return new AwsS3V3Adapter($client, $options['bucket_name'], '');
        }
        return new AwsS3Adapter($client, $options['bucket_name'], '', ['override_visibility_on_copy' => true]);
    }
}
