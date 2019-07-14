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

namespace Hyperf\View;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Hyperf\View\Engine\EngineInterface;
use Hyperf\View\Engine\SmartyEngine;
use Hyperf\View\Exception\EngineNotFindException;
use Psr\Container\ContainerInterface;

class Render
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $engine;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $engine = $config->get('view.engine', SmartyEngine::class);
        if (! $container->has($engine)) {
            throw new EngineNotFindException("{$engine} is not find.");
        }

        $this->engine = $engine;
        $this->mode = $config->get('view.mode', Mode::TASK);
        $this->config = $config->get('view.config', []);
        $this->container = $container;
    }

    public function view($template, $data)
    {
        switch ($this->mode) {
            case Mode::SYNC:
                /** @var EngineInterface $engine */
                $engine = $this->container->get($this->engine);
                $result = $engine->render($template, $data, $this->config);
                break;
            case Mode::TASK:
            default:
                $executor = $this->container->get(TaskExecutor::class);
                $result = $executor->execute(new Task([$this->engine, 'render'], [$path, $data, $this->config]));
        }

        return $result;
    }
}
