<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ConfigApollo\Listener;

use Hyperf\ConfigApollo\ClientInterface;
use Hyperf\ConfigApollo\Option;
use Hyperf\ConfigApollo\PipeMessage;
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
        if ($event instanceof OnPipeMessage && $event->data instanceof PipeMessage) {
            /** @var PipeMessage $data */
            $data = $event->data;

            if (! $data->isValid()) {
                return;
            }

            $option = $this->client->getOption();
            if (! $option instanceof Option) {
                return;
            }
            $cacheKey = $option->buildCacheKey($data->namespace);
            $cachedKey = ReleaseKey::get($cacheKey);
            if ($cachedKey && $cachedKey === $data->releaseKey) {
                return;
            }
            foreach ($data->configurations ?? [] as $key => $value) {
                $this->config->set($key, $this->formatValue($value));
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
            ReleaseKey::set($cacheKey, $data->releaseKey);
        }
    }

    /**
     * Format processing
     */
    private function formatValue($value)
    {
        if (! $this->config->get('apollo.strict_mode', false)) {
            return $value;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (is_numeric($value)) {
            $value = (strpos($value, '.') === false) ? (int) $value : (float) $value;
        }

        return $value;
    }

}
