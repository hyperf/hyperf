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

namespace Hyperf\Database\Commands;

use Hyperf\CodeParser\Project;
use Hyperf\Collection\Collection;
use Hyperf\Command\Annotation\AsCommand;
use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Events\ModelPruningFinished;
use Hyperf\Database\Events\ModelPruningStarting;
use Hyperf\Database\Exception\InvalidArgumentException;
use Hyperf\Database\Model\Model;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand('model:prune')]
class PruneCommand extends Command
{
    protected ?string $signature = 'model:prune
                                {--pool=default : The database connection pool to use}
                                {--model=* : Class names of the models to be pruned}
                                {--except=* : Class names of the models to be excluded from pruning}
                                {--path=* : Absolute path(s) to directories where models are located}
                                {--chunk=1000 : The number of models to retrieve per chunk of models to be deleted}
                                {--pretend : Display the number of prunable records found instead of deleting them}';

    protected string $description = 'Prune models that are no longer needed';

    public function __construct(protected readonly ConfigInterface $config)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $models = $this->models();

        if ($models->isEmpty()) {
            $this->output->info('No prunable models found.');

            return;
        }

        if ($this->option('pretend')) {
            $models->each(function ($model) {
                $this->pretendToPrune($model);
            });

            return;
        }
        if (! $this->eventDispatcher) {
            return;
        }
        $this->eventDispatcher->dispatch(new ModelPruningStarting($models->all()));

        $models->each(function ($model) {
            $this->pruneModel($model);
        });

        $this->eventDispatcher->dispatch(new ModelPruningFinished($models->all()));
    }

    /**
     * Prune the given model.
     *
     * @param class-string $model
     */
    protected function pruneModel(string $model): void
    {
        $instance = new $model();

        $chunkSize = property_exists($instance, 'prunableChunkSize')
            ? $instance->prunableChunkSize
            : $this->option('chunk');

        $total = $model::isPrunable()
            ? $instance->pruneAll($chunkSize)
            : 0;

        if ($total == 0) {
            $this->output->info("No prunable [{$model}] records found.");
        }
    }

    protected function getOption(string $name, string $key, string $pool = 'default', $default = null)
    {
        $result = $this->input->getOption($name);
        $nonInput = null;
        if ($result === $nonInput) {
            $result = $this->config->get("databases.{$pool}.{$key}", $default);
        }

        return $result;
    }

    /**
     * Determine the models that should be pruned.
     */
    protected function models(): Collection
    {
        $models = $this->option('model');
        $except = $this->option('except');

        $pool = $this->input->getOption('pool');

        if ($models && $except) {
            throw new InvalidArgumentException('The --models and --except options cannot be combined.');
        }

        if ($models) {
            return (new Collection($models))
                ->filter(static fn (string $model) => class_exists($model))
                ->values();
        }
        $project = new Project();
        $path = BASE_PATH . DIRECTORY_SEPARATOR . $this->getOption('path', 'commands.gen:model.path', $pool, 'app/Model');

        return (new Collection(Finder::create()->in($path)->files()->name('*.php')))
            ->map(function (SplFileInfo $model) use ($project) {
                return $project->namespace(str_replace(BASE_PATH, '', $model->getRealPath()));
            })
            ->when(! empty($except), fn (Collection $models) => $models->reject(fn ($model) => in_array($model, $except)))
            ->filter(fn ($model) => $this->isPrunable($model))
            ->values();
    }

    /**
     * Display how many models will be pruned.
     *
     * @param class-string<Model> $model
     */
    protected function pretendToPrune(string $model): void
    {
        $instance = new $model();

        $count = $instance->prunable()
            ->when($model::isSoftDeletable(), function ($query) {
                $query->withTrashed();
            })->count();

        if ($count === 0) {
            $this->output->info("No prunable [{$model}] records found.");
        } else {
            $this->output->info("{$count} [{$model}] records will be pruned.");
        }
    }

    /**
     * Determine if the given model is prunable.
     */
    private function isPrunable(string $model): bool
    {
        return class_exists($model)
            && is_a($model, Model::class, true)
            && ! (new ReflectionClass($model))->isAbstract()
            && $model::isPrunable();
    }
}
