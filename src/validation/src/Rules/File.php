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

namespace Hyperf\Validation\Rules;

use Hyperf\Collection\Arr;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Context\ApplicationContext;
use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Contract\DataAwareRule;
use Hyperf\Validation\Contract\Rule;
use Hyperf\Validation\Contract\ValidatorAwareRule;
use Hyperf\Validation\Validator;
use Hyperf\Validation\ValidatorFactory;
use InvalidArgumentException;

use function Hyperf\Collection\collect;

class File implements Rule, DataAwareRule, ValidatorAwareRule
{
    use Conditionable;
    use Macroable;

    /**
     * The callback that will generate the "default" version of the file rule.
     *
     * @var null|array|callable|string
     */
    public static $defaultCallback;

    /**
     * The MIME types that the given file should match. This array may also contain file extensions.
     */
    protected array $allowedMimetypes = [];

    /**
     * The minimum size in kilobytes that the file can be.
     */
    protected ?int $minimumFileSize = null;

    /**
     * The maximum size in kilobytes that the file can be.
     */
    protected ?int $maximumFileSize = null;

    /**
     * An array of custom rules that will be merged into the validation rules.
     */
    protected array $customRules = [];

    /**
     * The error message after validation, if any.
     */
    protected array $messages = [];

    /**
     * The data under validation.
     */
    protected array $data = [];

    /**
     * The validator performing the validation.
     */
    protected ?Validator $validator = null;

    /**
     * Set the default callback to be used for determining the file default rules.
     *
     * If no arguments are passed, the default file rule configuration will be returned.
     *
     * @param null|callable|static $callback
     * @return null|static
     */
    public static function defaults(null|callable|File $callback = null)
    {
        if (is_null($callback)) {
            return static::default();
        }

        if (! is_callable($callback) && ! $callback instanceof static) {
            throw new InvalidArgumentException('The given callback should be callable or an instance of ' . static::class);
        }

        static::$defaultCallback = $callback;
    }

    /**
     * Get the default configuration of the file rule.
     */
    public static function default()
    {
        $file = is_callable(static::$defaultCallback)
            ? call_user_func(static::$defaultCallback)
            : static::$defaultCallback;

        return $file instanceof Rule ? $file : new self();
    }

    /**
     * Limit the uploaded file to only image types.
     */
    public static function image(): ImageFile
    {
        return new ImageFile();
    }

    /**
     * Limit the uploaded file to the given MIME types or file extensions.
     *
     * @param array<int, string>|string $mimetypes
     */
    public static function types(array|string $mimetypes): static
    {
        return \Hyperf\Tappable\tap(new static(), fn ($file) => $file->allowedMimetypes = (array) $mimetypes);
    }

    /**
     * Indicate that the uploaded file should be exactly a certain size in kilobytes.
     *
     * @return $this
     */
    public function size(int|string $size): static
    {
        $this->minimumFileSize = $this->toKilobytes($size);
        $this->maximumFileSize = $this->minimumFileSize;

        return $this;
    }

    /**
     * Indicate that the uploaded file should be between a minimum and maximum size in kilobytes.
     *
     * @return $this
     */
    public function between(int|string $minSize, int|string $maxSize): static
    {
        $this->minimumFileSize = $this->toKilobytes($minSize);
        $this->maximumFileSize = $this->toKilobytes($maxSize);

        return $this;
    }

    /**
     * Indicate that the uploaded file should be no less than the given number of kilobytes.
     *
     * @return $this
     */
    public function min(int|string $size): static
    {
        $this->minimumFileSize = (int) $this->toKilobytes($size);

        return $this;
    }

    /**
     * Indicate that the uploaded file should be no more than the given number of kilobytes.
     *
     * @return $this
     */
    public function max(int|string $size): static
    {
        $this->maximumFileSize = (int) $this->toKilobytes($size);

        return $this;
    }

    /**
     * Specify additional validation rules that should be merged with the default rules during validation.
     *
     * @param mixed $rules
     * @return $this
     */
    public function rules($rules): static
    {
        $this->customRules = array_merge($this->customRules, Arr::wrap($rules));

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes(string $attribute, mixed $value): bool
    {
        $this->messages = [];

        $test = $this->buildValidationRules();

        $validator = ApplicationContext::getContainer()->get(ValidatorFactory::class)->make(
            $this->data,
            [$attribute => $test],
            $this->validator->customMessages,
            $this->validator->customAttributes
        );

        if ($validator->fails()) {
            return $this->fail($validator->messages()->all());
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): array|string
    {
        return $this->messages;
    }

    /**
     * Set the current validator.
     *
     * @return $this
     */
    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the current data under validation.
     *
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Convert a potentially human-friendly file size to kilobytes.
     *
     * @param int|string $size
     * @return mixed
     */
    protected function toKilobytes($size)
    {
        if (! is_string($size)) {
            return $size;
        }

        $value = floatval($size);

        return round(match (true) {
            Str::endsWith($size, 'kb') => $value * 1,
            Str::endsWith($size, 'mb') => $value * 1000,
            Str::endsWith($size, 'gb') => $value * 1000000,
            Str::endsWith($size, 'tb') => $value * 1000000000,
            default => throw new InvalidArgumentException('Invalid file size suffix.'),
        });
    }

    /**
     * Build the array of underlying validation rules based on the current state.
     *
     * @return array
     */
    protected function buildValidationRules()
    {
        $rules = ['file'];

        $rules = array_merge($rules, $this->buildMimetypes());

        $rules[] = match (true) {
            is_null($this->minimumFileSize) && is_null($this->maximumFileSize) => null,
            is_null($this->maximumFileSize) => "min:{$this->minimumFileSize}",
            is_null($this->minimumFileSize) => "max:{$this->maximumFileSize}",
            $this->minimumFileSize !== $this->maximumFileSize => "between:{$this->minimumFileSize},{$this->maximumFileSize}",
            default => "size:{$this->minimumFileSize}",
        };

        return array_merge(array_filter($rules), $this->customRules);
    }

    /**
     * Separate the given mimetypes from extensions and return an array of correct rules to validate against.
     *
     * @return array
     */
    protected function buildMimetypes()
    {
        if (count($this->allowedMimetypes) === 0) {
            return [];
        }

        $rules = [];

        $mimetypes = array_filter(
            $this->allowedMimetypes,
            fn ($type) => str_contains($type, '/')
        );

        $mimes = array_diff($this->allowedMimetypes, $mimetypes);

        if (count($mimetypes) > 0) {
            $rules[] = 'mimetypes:' . implode(',', $mimetypes);
        }

        if (count($mimes) > 0) {
            $rules[] = 'mimes:' . implode(',', $mimes);
        }

        return $rules;
    }

    /**
     * Adds the given failures, and return false.
     *
     * @param array|string $messages
     * @return bool
     */
    protected function fail($messages)
    {
        $messages = collect(Arr::wrap($messages))->map(function ($message) {
            return $this->validator->getTranslator()->get($message);
        })->all();

        $this->messages = array_merge($this->messages, $messages);

        return false;
    }
}
