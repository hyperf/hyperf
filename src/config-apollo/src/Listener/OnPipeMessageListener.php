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

namespace Hyperf\ConfigApollo\Listener;

use Hyperf\ConfigApollo\ClientInterface;
use Hyperf\ConfigApollo\Option;
use Hyperf\ConfigApollo\ReleaseKey;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;

/**
 * @Listener
 */
class OnPipeMessageListener implements ListenerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var \Hyperf\ConfigApollo\ClientInterface
     */
    private $client;

    public function __construct(ConfigInterface $config, StdoutLoggerInterface $logger, ClientInterface $client)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            OnPipeMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        /** @var OnPipeMessage $event */
        if (! isset($event->data['configurations'], $event->data['releaseKey'], $event->data['namespace'])) {
            return;
        }
        if (! $event->data['configurations'] || ! $event->data['releaseKey'] || ! $event->data['namespace']) {
            return;
        }
        $option = $this->client->getOption();
        if (! $option instanceof Option) {
            return;
        }
        $cacheKey = $option->buildCacheKey($event->data['namespace']);
        $cachedKey = ReleaseKey::get($cacheKey);
        if ($cachedKey && $cachedKey === $event->data['releaseKey']) {
            return;
        }
        foreach ($event->data['configurations'] ?? [] as $key => $value) {
            $this->config->set($key, $value);
            $this->logger->debug(sprintf('Config [%s] is updated', $key));
        }
        ReleaseKey::set($cacheKey, $event->data['releaseKey']);
    }
}
