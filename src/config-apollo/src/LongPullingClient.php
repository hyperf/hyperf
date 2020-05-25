<?php


namespace Hyperf\ConfigApollo;


class LongPullingClient extends AbstractClient
{

    /**
     * @var array
     */
    protected $notifications;


    public function fetch(array $namespaces, array $callbacks = []): void
    {
        $httpClientFactory = $this->httpClientFactory;
        $client = $httpClientFactory([
            'timeout' => $this->option->getIntervalTimeout(),
        ]);
        if (!$client instanceof \GuzzleHttp\Client) {
            throw new \RuntimeException('Invalid http client.');
        }
        foreach ($namespaces as $namespace) {
            if (!isset($this->notifications[$namespace])) {
                $this->notifications[$namespace] = ['namespaceName' => $namespace, 'notificationId' => -1];
            }
        }

        while (true) {
            $url = sprintf('%s/notifications/v2?%s',
                $this->option->getServer(),
                http_build_query([
                    'appId' => $this->option->getAppid(),
                    'cluster' => $this->option->getCluster(),
                    'notifications' => json_encode(array_values($this->notifications)),
                ])
            );

            // Ignore the timeout error
            try {
                $response = $client->get($url);
                if ($response->getStatusCode() === 200) {
                    $notifications = json_decode((string)$response->getBody(), true);
                    // Ignore the first pull
                    if (!empty($this->notifications) && current($this->notifications)['notificationId'] !== -1) {
                        $this->pull($namespaces, $callbacks);
                    }
                    array_walk($notifications, function (&$notification) {
                        unset($notification['messages']);
                    });
                    $this->notifications = array_merge($this->notifications, array_column($notifications, null, 'namespaceName'));
                } elseif ($response['statusCode'] === 304) {
                    // ignore 304
                }
            } catch (\Exception $exception) {
            }

        }
    }

}