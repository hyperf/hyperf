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

use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Str;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    /**
     * The name of the command.
     *
     * @var string
     */
    protected $name;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var SymfonyStyle
     */
    protected $output;

    /**
     * The default verbosity of output commands.
     *
     * @var int
     */
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * Execution in a coroutine environment.
     *
     * @var bool
     */
    protected $coroutine = true;

    /**
     * @var null|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var int
     */
    protected $hookFlags;

    /**
     * The name and signature of the command.
     *
     * @var null|string
     */
    protected $signature;

    /**
     * The mapping between human readable verbosity levels and Symfony's OutputInterface.
     *
     * @var array
     */
    protected $verbosityMap
        = [
            'v' => OutputInterface::VERBOSITY_VERBOSE,
            'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            'vvv' => OutputInterface::VERBOSITY_DEBUG,
            'quiet' => OutputInterface::VERBOSITY_QUIET,
            'normal' => OutputInterface::VERBOSITY_NORMAL,
        ];

    public function __construct(string $name = null)
    {
        if (! $name && $this->name) {
            $name = $this->name;
        }

        if (! is_int($this->hookFlags)) {
            $this->hookFlags = swoole_hook_flags();
        }

        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($name);
        }
    }

    /**
     * Run the console command.
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->output = new SymfonyStyle($input, $output);

        return parent::run($this->input = $input, $this->output);
    }

    /**
     * Confirm a question with the user.
     */
    public function confirm(string $question, bool $default = false): bool
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param null|mixed $default
     */
    public function ask(string $question, $default = null)
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param null|mixed $default
     */
    public function anticipate(string $question, array $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param null|mixed $default
     */
    public function askWithCompletion(string $question, array $choices, $default = null)
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     */
    public function secret(string $question, bool $fallback = true)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a multiple choice from an array of answers.
     * @param null|mixed $default
     */
    public function choiceMultiple(
        string $question,
        array $choices,
        $default = null,
        ?int $attempts = null
    ): array {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect(true);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param null|mixed $default
     */
    public function choice(
        string $question,
        array $choices,
        $default = null,
        ?int $attempts = null
    ): string {
        return $this->choiceMultiple($question, $choices, $default, $attempts)[0];
    }

    /**
     * Format input to textual table.
     *
     * @param mixed $rows
     * @param mixed $tableStyle
     */
    public function table(array $headers, $rows, $tableStyle = 'default', array $columnStyles = []): void
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Write a string as standard output.
     *
     * @param mixed $string
     * @param null|mixed $style
     * @param null|mixed $verbosity
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $styled = $style ? "<{$style}>{$string}</{$style}>" : $string;
        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * Write a string as information output.
     *
     * @param mixed $string
     * @param null|mixed $verbosity
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as comment output.
     *
     * @param mixed $string
     * @param null|mixed $verbosity
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param mixed $string
     * @param null|mixed $verbosity
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param mixed $string
     * @param null|mixed $verbosity
     */
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param mixed $string
     * @param null|mixed $verbosity
     */
    public function warn($string, $verbosity = null)
    {
        if (! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }
        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Write a string in an alert box.
     *
     * @param mixed $string
     */
    public function alert($string)
    {
        $length = Str::length(strip_tags($string)) + 12;
        $this->comment(str_repeat('*', $length));
        $this->comment('*     ' . $string . '     *');
        $this->comment(str_repeat('*', $length));
        $this->output->newLine();
    }

    /**
     * Call another console command.
     */
    public function call(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run($this->createInputFromArguments($arguments), $this->output);
    }

    /**
     * Handle the current command.
     */
    abstract public function handle();

    /**
     * Set the verbosity level.
     *
     * @param mixed $level
     */
    protected function setVerbosity($level)
    {
        $this->verbosity = $this->parseVerbosity($level);
    }

    /**
     * Get the verbosity level in terms of Symfony's OutputInterface level.
     *
     * @param null|mixed $level
     */
    protected function parseVerbosity($level = null): int
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (! is_int($level)) {
            $level = $this->verbosity;
        }
        return $level;
    }

    /**
     * Create an input instance from the given arguments.
     */
    protected function createInputFromArguments(array $arguments): ArrayInput
    {
        return tap(new ArrayInput(array_merge($this->context(), $arguments)), function (InputInterface $input) {
            if ($input->hasParameterOption(['--no-interaction'], true)) {
                $input->setInteractive(false);
            }
        });
    }

    /**
     * Get all of the context passed to the command.
     */
    protected function context(): array
    {
        return collect($this->input->getOptions())->only([
            'ansi',
            'no-ansi',
            'no-interaction',
            'quiet',
            'verbose',
        ])->filter()->mapWithKeys(function ($value, $key) {
            return ["--{$key}" => $value];
        })->all();
    }

    /**
     * Specify the arguments and options on the command.
     */
    protected function specifyParameters(): void
    {
        // We will loop through all of the arguments and options for the command and
        // set them all on the base command instance. This specifies what can get
        // passed into these commands as "parameters" to control the execution.
        if (method_exists($this, 'getArguments')) {
            foreach ($this->getArguments() ?? [] as $arguments) {
                call_user_func_array([$this, 'addArgument'], $arguments);
            }
        }

        if (method_exists($this, 'getOptions')) {
            foreach ($this->getOptions() ?? [] as $options) {
                call_user_func_array([$this, 'addOption'], $options);
            }
        }
    }

    /**
     * Configure the console command using a fluent definition.
     */
    protected function configureUsingFluentDefinition()
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($this->name = $name);

        // After parsing the signature we will spin through the arguments and options
        // and set them on this command. These will already be changed into proper
        // instances of these "InputArgument" and "InputOption" Symfony classes.
        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }

    protected function configure()
    {
        parent::configure();
        if (! isset($this->signature)) {
            $this->specifyParameters();
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $callback = function () {
            try {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new Event\BeforeHandle($this));
                call([$this, 'handle']);
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new Event\AfterHandle($this));
            } catch (\Throwable $exception) {
                if (! $this->eventDispatcher) {
                    throw $exception;
                }

                $this->eventDispatcher->dispatch(new Event\FailToHandle($this, $exception));
                return $exception->getCode();
            } finally {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new Event\AfterExecute($this));
            }

            return 0;
        };

        if ($this->coroutine && ! Coroutine::inCoroutine()) {
            run($callback, $this->hookFlags);
            return 0;
        }

        return $callback();
    }
}
