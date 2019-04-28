<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer;

use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use RuntimeException;
use Zipkin\Reporters\Http\ClientFactory;

class HttpClientFactory implements ClientFactory
{
    /**
     * @var GuzzleClientFactory
     */
    private $guzzleClientFactory;

    public function __construct(GuzzleClientFactory $guzzleClientFactory)
    {
        $this->guzzleClientFactory = $guzzleClientFactory;
    }

    /**
     * @return callable
     */
    public function build(array $options)
    {
        return function ($payload) use ($options) {
            $url = $options['endpoint_url'];
            unset($options['endpoint_url']);
            $client = $this->guzzleClientFactory->create($options);
            $additionalHeaders = (isset($options['headers']) ? $options['headers'] : []);
            $requiredHeaders = [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($payload),
            ];
            $headers = array_merge($additionalHeaders, $requiredHeaders);
            $response = $client->post($url, [
                'body' => $payload,
                'headers' => $headers,
                // If 'no_aspect' option is true, then the HttpClientAspect will not modify the client options.
                'no_aspect' => true,
            ]);
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 202) {
                throw new RuntimeException(
                    sprintf('Reporting of spans failed, status code %d', $statusCode)
                );
            }
        };
    }
}
