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

namespace Hyperf\Paginator;

use Hyperf\Contract\Arrayable;
use JsonException;
use UnexpectedValueException;

use function Hyperf\Collection\collect;

class Cursor implements Arrayable
{
    /**
     * Create a new cursor instance.
     * @param array $parameters the parameters associated with the cursor
     * @param bool $pointsToNextItems determine whether the cursor points to the next or previous set of items
     */
    public function __construct(
        protected array $parameters,
        protected bool $pointsToNextItems = true
    ) {
    }

    /**
     * Get the given parameter from the cursor.
     */
    public function parameter(string $parameterName): ?string
    {
        if (! array_key_exists($parameterName, $this->parameters)) {
            throw new UnexpectedValueException("Unable to find parameter [{$parameterName}] in pagination item.");
        }

        return (string) $this->parameters[$parameterName];
    }

    /**
     * Get the given parameters from the cursor.
     */
    public function parameters(array $parameterNames): array
    {
        return collect($parameterNames)->map(function ($parameterName) {
            return $this->parameter($parameterName);
        })->toArray();
    }

    /**
     * Determine whether the cursor points to the next set of items.
     */
    public function pointsToNextItems(): bool
    {
        return $this->pointsToNextItems;
    }

    /**
     * Determine whether the cursor points to the previous set of items.
     */
    public function pointsToPreviousItems(): bool
    {
        return ! $this->pointsToNextItems;
    }

    /**
     * Get the array representation of the cursor.
     */
    public function toArray(): array
    {
        return array_merge($this->parameters, [
            '_pointsToNextItems' => $this->pointsToNextItems,
        ]);
    }

    /**
     * Get the encoded string representation of the cursor to construct a URL.
     */
    public function encode(): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($this->toArray(), JSON_THROW_ON_ERROR)));
    }

    /**
     * Get a cursor instance from the encoded string representation.
     */
    public static function fromEncoded(?string $encodedString): ?static
    {
        if (! is_string($encodedString)) {
            return null;
        }

        try {
            $parameters = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $encodedString)), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return null;
        }

        $pointsToNextItems = $parameters['_pointsToNextItems'];

        unset($parameters['_pointsToNextItems']);

        return new static($parameters, $pointsToNextItems);
    }
}
