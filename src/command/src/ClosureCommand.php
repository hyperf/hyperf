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

namespace Hyperf\Command;

use Closure;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Schedule;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use ReflectionFunction;

use function Hyperf\Tappable\tap;

final class ClosureCommand extends Command
{
    private ParameterParser $parameterParser;

    public function __construct(
        private ContainerInterface $container,
        string $signature,
        private Closure $closure
    ) {
        $this->signature = $signature;
        $this->parameterParser = $container->get(ParameterParser::class);

        parent::__construct();

        $options = $this->parameterParser->parseClosureOptions($closure);
        $definition = $this->getDefinition();
        foreach ($options as $option) {
            $name = $option->getName();
            $snakeName = Str::snake($option->getName(), '-');

            if (
                $definition->hasOption($name)
                || $definition->hasArgument($name)
                || $definition->hasOption($snakeName)
                || $definition->hasArgument($snakeName)
            ) {
                continue;
            }

            $definition->addOption($option);
        }
    }

    public function handle()
    {
        $inputs = array_merge($this->input->getArguments(), $this->input->getOptions());
        $parameters = $this->parameterParser->parseClosureParameters($this->closure, $inputs);
        $ref = new ReflectionFunction($this->closure);

        if ($ref->isStatic()) {
            ($this->closure)(...$parameters);
            return;
        }

        $this->closure->call($this, ...$parameters);
    }

    public function describe(string $description): self
    {
        $this->setDescription($description);

        return $this;
    }

    /**
     * @param null|callable(Crontab $crontab):Crontab $callback
     */
    public function cron(string $rule, array $arguments = [], ?callable $callback = null): self
    {
        tap(
            Schedule::command($this->getName(), $arguments)
                ->setName($this->getName())
                ->setRule($rule)
                ->setMemo($this->getDescription()),
            $callback
        );

        return $this;
    }
}
