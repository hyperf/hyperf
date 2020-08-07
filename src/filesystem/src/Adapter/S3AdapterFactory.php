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

use Aws\S3\S3Client;
use Hyperf\Guzzle\CoroutineHandler;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class S3AdapterFactory
{
    public function make(array $options): AdapterInterface
    {
        $options = array_merge($options, ['http_handler' => new CoroutineHandler()]);
        $client = new S3Client($options);
        return new AwsS3Adapter($client, $options['bucket_name'], '', ['override_visibility_on_copy' => true]);
    }
}
