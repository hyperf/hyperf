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

namespace Hyperf\Command\Concerns;

use Closure;
use Hyperf\Contract\Arrayable;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

trait InteractsWithIO
{
    protected ?InputInterface $input = null;

    /**
     * @var null|SymfonyStyle
     */
    protected ?OutputInterface $output = null;

    /**
     * The default verbosity of output commands.
     */
    protected int $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * The mapping between human readable verbosity levels and Symfony's OutputInterface.
     */
    protected array $verbosityMap = [
        'v' => OutputInterface::VERBOSITY_VERBOSE,
        'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    /**
     * Determine if the given argument is present.
     *
     * @param int|string $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     *
     * @param null|string $key
     * @return null|array|bool|string
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all of the arguments passed to the command.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->argument();
    }

    /**
     * Determine if the given option is present.
     *
     * @param string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * Get the value of a command option.
     *
     * @param null|string $key
     * @return null|array|bool|string
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all of the options passed to the command.
     *
     * @return array
     */
    public function options()
    {
        return $this->option();
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool $default
     * @return bool
     */
    public function confirm($question, $default = false)
    {
        return $this->output?->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param string $question
     * @param null|string $default
     * @return mixed
     */
    public function ask($question, $default = null)
    {
        return $this->output?->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $question
     * @param array|callable $choices
     * @param null|string $default
     * @return mixed
     */
    public function anticipate($question, $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $question
     * @param array|callable $choices
     * @param null|string $default
     * @return mixed
     */
    public function askWithCompletion($question, $choices, $default = null)
    {
        $question = new Question($question, $default);

        is_callable($choices)
            ? $question->setAutocompleterCallback($choices)
            : $question->setAutocompleterValues($choices);

        return $this->output?->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param string $question
     * @param bool $fallback
     * @return mixed
     */
    public function secret($question, $fallback = true)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output?->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string $question
     * @param null|int|string $default
     * @param null|mixed $attempts
     * @param bool $multiple
     * @return array|string
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false)
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output?->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array $headers
     * @param array|Arrayable $rows
     * @param string|TableStyle $tableStyle
     */
    public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
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
     * Execute a given callback while advancing a progress bar.
     *
     * @param int|iterable $totalSteps
     * @return mixed|void
     */
    public function withProgressBar($totalSteps, Closure $callback)
    {
        $bar = $this->output?->createProgressBar(
            is_iterable($totalSteps) ? count($totalSteps) : $totalSteps
        );

        $bar->start();

        if (is_iterable($totalSteps)) {
            foreach ($totalSteps as $value) {
                $callback($value, $bar);

                $bar->advance();
            }
        } else {
            $callback($bar);
        }

        $bar->finish();

        if (is_iterable($totalSteps)) {
            return $totalSteps;
        }
    }

    /**
     * Write a string as information output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as standard output.
     *
     * @param string $string
     * @param null|string $style
     * @param null|int|string $verbosity
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $styled = $style ? "<{$style}>{$string}</{$style}>" : $string;

        $this->output?->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * Write a string as comment output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function warn($string, $verbosity = null)
    {
        if (! $this->output?->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->output?->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Write a string in an alert box.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function alert($string, $verbosity = null)
    {
        $length = Str::length(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length), $verbosity);
        $this->comment('*     ' . $string . '     *', $verbosity);
        $this->comment(str_repeat('*', $length), $verbosity);

        $this->comment('', $verbosity);
    }

    /**
     * Write a blank line.
     *
     * @param int $count
     * @return $this
     */
    public function newLine($count = 1)
    {
        $this->output?->newLine($count);

        return $this;
    }

    /**
     * Set the input interface implementation.
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Set the output interface implementation.
     * @param SymfonyStyle $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Get the output implementation.
     *
     * @return null|SymfonyStyle
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set the verbosity level.
     *
     * @param int|string $level
     */
    protected function setVerbosity($level)
    {
        $this->verbosity = $this->parseVerbosity($level);
    }

    /**
     * Get the verbosity level in terms of Symfony's OutputInterface level.
     *
     * @param null|int|string $level
     * @return int
     */
    protected function parseVerbosity($level = null)
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (! is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }
}
