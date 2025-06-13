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

namespace Hyperf\View;

use Hyperf\Context\ResponseContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Hyperf\View\Engine\EngineInterface;
use Hyperf\View\Engine\NoneEngine;
use Hyperf\View\Exception\EngineNotFindException;
use Hyperf\View\Exception\RenderException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Render implements RenderInterface
{
    protected string $engine;

    protected string $mode;

    protected array $config;

    public function __construct(protected ContainerInterface $container, ConfigInterface $config)
    {
        $engine = $config->get('view.engine', NoneEngine::class);
        if (! $container->has($engine)) {
            throw new EngineNotFindException("{$engine} engine is not found.");
        }

        $this->engine = $engine;
        $this->mode = $config->get('view.mode', Mode::TASK);
        $this->config = $config->get('view.config', []);
    }

    public function render(string $template, array $data = []): ResponseInterface
    {
        return ResponseContext::get()
            ->addHeader('content-type', $this->getContentType())
            ->setBody(new SwooleStream($this->getContents($template, $data)));
    }

    public function getContents(string $template, array $data = []): string
    {
        try {
            switch ($this->mode) {
                case Mode::SYNC:
                    /** @var EngineInterface $engine */
                    $engine = $this->container->get($this->engine);
                    $result = $engine->render($template, $data, $this->config);
                    break;
                case Mode::TASK:
                default:
                    $executor = $this->container->get(TaskExecutor::class);
                    $result = $executor->execute(new Task([$this->engine, 'render'], [$template, $data, $this->config]));
                    break;
            }

            return $result;
        } catch (Throwable $throwable) {
            throw new RenderException($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
        }
    }

    public function getContentType(): string
    {
        $charset = ! empty($this->config['charset']) ? '; charset=' . $this->config['charset'] : '';

        return 'text/html' . $charset;
    }
}
