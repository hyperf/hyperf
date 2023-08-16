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
namespace Hyperf\Testing\Http;

use ArrayAccess;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\HttpServer\Response;
use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;
use Hyperf\Tappable\Tappable;
use Hyperf\Testing\AssertableJsonString;
use Hyperf\Testing\Constraint\SeeInOrder;
use Hyperf\Testing\Fluent\AssertableJson;
use LogicException;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * @mixin Response
 */
class TestResponse implements ArrayAccess
{
    use Concerns\AssertsStatusCodes, Tappable, Macroable {
        __call as macroCall;
    }

    protected ?array $decoded = null;

    /**
     * The streamed content of the response.
     *
     * @var null|string
     */
    protected $streamedContent;

    public function __construct(protected ResponseInterface $response)
    {
    }

    /**
     * Handle dynamic calls into macros or pass missing methods to the base response.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $args);
        }

        return $this->response->{$method}(...$args);
    }

    /**
     * Dynamically access base response parameters.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->response->{$key};
    }

    /**
     * Proxy isset() checks to the underlying base response.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->response->{$key});
    }

    /**
     * Get the content of the response.
     */
    public function getContent(): string
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * Assert that the given string matches the response content.
     *
     * @param string $value
     * @return $this
     */
    public function assertContent($value)
    {
        PHPUnit::assertSame($value, $this->getContent());

        return $this;
    }

    /**
     * Assert that the given string matches the streamed response content.
     *
     * @param string $value
     * @return $this
     */
    public function assertStreamedContent($value)
    {
        PHPUnit::assertSame($value, $this->streamedContent());

        return $this;
    }

    /**
     * Assert that the given string or array of strings are contained within the response.
     *
     * @param array|string $value
     * @param bool $escape
     * @return $this
     */
    public function assertSee($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map(fn ($value) => htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true), $value) : $value;

        foreach ($values as $value) {
            PHPUnit::assertStringContainsString((string) $value, $this->getContent());
        }

        return $this;
    }

    /**
     * Assert that the given strings are contained in order within the response.
     *
     * @param bool $escape
     * @return $this
     */
    public function assertSeeInOrder(array $values, $escape = true)
    {
        $values = $escape ? array_map(fn ($value) => htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true), $values) : $values;

        PHPUnit::assertThat($values, new SeeInOrder($this->getContent()));

        return $this;
    }

    /**
     * Assert that the given string or array of strings are contained within the response text.
     *
     * @param array|string $value
     * @param bool $escape
     * @return $this
     */
    public function assertSeeText($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map(fn ($value) => htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true), $value) : $value;

        $content = strip_tags($this->getContent());

        foreach ($values as $value) {
            PHPUnit::assertStringContainsString((string) $value, $content);
        }

        return $this;
    }

    /**
     * Assert that the given strings are contained in order within the response text.
     *
     * @param bool $escape
     * @return $this
     */
    public function assertSeeTextInOrder(array $values, $escape = true)
    {
        $values = $escape ? array_map(fn ($value) => htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true), $values) : $values;

        PHPUnit::assertThat($values, new SeeInOrder(strip_tags($this->getContent())));

        return $this;
    }

    /**
     * Assert that the given string or array of strings are not contained within the response.
     *
     * @param array|string $value
     * @param bool $escape
     * @return $this
     */
    public function assertDontSee($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map(fn ($value) => htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true), $value) : $value;

        foreach ($values as $value) {
            PHPUnit::assertStringNotContainsString((string) $value, $this->getContent());
        }

        return $this;
    }

    /**
     * Assert that the given string or array of strings are not contained within the response text.
     *
     * @param array|string $value
     * @param bool $escape
     * @return $this
     */
    public function assertDontSeeText($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map(fn ($value) => htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true), $value) : $value;

        $content = strip_tags($this->getContent());

        foreach ($values as $value) {
            PHPUnit::assertStringNotContainsString((string) $value, $content);
        }

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param array|callable $value
     * @param bool $strict
     * @return $this
     */
    public function assertJson($value, $strict = false)
    {
        $json = $this->decodeResponseJson();

        if (is_array($value)) {
            $json->assertSubset($value, $strict);
        } else {
            $assert = AssertableJson::fromAssertableJsonString($json);

            $value($assert);

            if (Arr::isAssoc($assert->toArray())) {
                $assert->interacted();
            }
        }

        return $this;
    }

    /**
     * Assert that the expected value and type exists at the given path in the response.
     *
     * @param string $path
     * @param mixed $expect
     * @return $this
     */
    public function assertJsonPath($path, $expect)
    {
        $this->decodeResponseJson()->assertPath($path, $expect);

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @return $this
     */
    public function assertExactJson(array $data)
    {
        $this->decodeResponseJson()->assertExact($data);

        return $this;
    }

    /**
     * Assert that the response has the similar JSON as given.
     *
     * @return $this
     */
    public function assertSimilarJson(array $data)
    {
        $this->decodeResponseJson()->assertSimilar($data);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @return $this
     */
    public function assertJsonFragment(array $data)
    {
        $this->decodeResponseJson()->assertFragment($data);

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param bool $exact
     * @return $this
     */
    public function assertJsonMissing(array $data, $exact = false)
    {
        $this->decodeResponseJson()->assertMissing($data, $exact);

        return $this;
    }

    /**
     * Assert that the response does not contain the exact JSON fragment.
     *
     * @return $this
     */
    public function assertJsonMissingExact(array $data)
    {
        $this->decodeResponseJson()->assertMissingExact($data);

        return $this;
    }

    /**
     * Assert that the response does not contain the given path.
     *
     * @return $this
     */
    public function assertJsonMissingPath(string $path)
    {
        $this->decodeResponseJson()->assertMissingPath($path);

        return $this;
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param null|array $responseData
     * @return $this
     */
    public function assertJsonStructure(array $structure = null, $responseData = null)
    {
        $this->decodeResponseJson()->assertStructure($structure, $responseData);

        return $this;
    }

    /**
     * Assert that the response JSON has the expected count of items at the given key.
     *
     * @param null|string $key
     * @return $this
     */
    public function assertJsonCount(int $count, $key = null)
    {
        $this->decodeResponseJson()->assertCount($count, $key);

        return $this;
    }

    /**
     * Assert that the response has the given JSON validation errors.
     *
     * @param array|string $errors
     * @param string $responseKey
     * @return $this
     */
    public function assertJsonValidationErrors($errors, $responseKey = 'errors')
    {
        $errors = Arr::wrap($errors);

        PHPUnit::assertNotEmpty($errors, 'No validation errors were provided.');

        $jsonErrors = Arr::get($this->json(), $responseKey) ?? [];

        $errorMessage = $jsonErrors
                ? 'Response has the following JSON validation errors:' .
                        PHP_EOL . PHP_EOL . json_encode($jsonErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
                : 'Response does not have JSON validation errors.';

        foreach ($errors as $key => $value) {
            if (is_int($key)) {
                $this->assertJsonValidationErrorFor($value, $responseKey);

                continue;
            }

            $this->assertJsonValidationErrorFor($key, $responseKey);

            foreach (Arr::wrap($value) as $expectedMessage) {
                $errorMissing = true;

                foreach (Arr::wrap($jsonErrors[$key]) as $jsonErrorMessage) {
                    if (Str::contains($jsonErrorMessage, $expectedMessage)) {
                        $errorMissing = false;

                        break;
                    }
                }
            }

            if ($errorMissing) { /* @phpstan-ignore-line */
                PHPUnit::fail(
                    "Failed to find a validation error in the response for key and message: '{$key}' => '{$expectedMessage}'" . PHP_EOL . PHP_EOL . $errorMessage  /* @phpstan-ignore-line */
                );
            }
        }

        return $this;
    }

    /**
     * Assert the response has any JSON validation errors for the given key.
     *
     * @param string $key
     * @param string $responseKey
     * @return $this
     */
    public function assertJsonValidationErrorFor($key, $responseKey = 'errors')
    {
        $jsonErrors = Arr::get($this->json(), $responseKey) ?? [];

        $errorMessage = $jsonErrors
            ? 'Response has the following JSON validation errors:' .
            PHP_EOL . PHP_EOL . json_encode($jsonErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
            : 'Response does not have JSON validation errors.';

        PHPUnit::assertArrayHasKey(
            $key,
            $jsonErrors,
            "Failed to find a validation error in the response for key: '{$key}'" . PHP_EOL . PHP_EOL . $errorMessage
        );

        return $this;
    }

    /**
     * Assert that the response has no JSON validation errors for the given keys.
     *
     * @param null|array|string $keys
     * @param string $responseKey
     * @return $this
     */
    public function assertJsonMissingValidationErrors($keys = null, $responseKey = 'errors')
    {
        if ($this->getContent() === '') {
            PHPUnit::assertTrue(true);

            return $this;
        }

        $json = $this->json();

        if (! Arr::has($json, $responseKey)) {
            PHPUnit::assertTrue(true);

            return $this;
        }

        $errors = Arr::get($json, $responseKey, []);

        if (is_null($keys) && count($errors) > 0) {
            PHPUnit::fail(
                'Response has unexpected validation errors: ' . PHP_EOL . PHP_EOL .
                json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
        }

        foreach (Arr::wrap($keys) as $key) {
            PHPUnit::assertFalse(
                isset($errors[$key]),
                "Found unexpected validation error for key: '{$key}'"
            );
        }

        return $this;
    }

    /**
     * Assert that the given key is a JSON array.
     *
     * @param null|mixed $key
     * @return $this
     */
    public function assertJsonIsArray($key = null)
    {
        $data = $this->json($key);

        $encodedData = json_encode($data);

        PHPUnit::assertTrue(
            is_array($data)
            && str_starts_with($encodedData, '[')
            && str_ends_with($encodedData, ']')
        );

        return $this;
    }

    /**
     * Assert that the given key is a JSON object.
     *
     * @param null|mixed $key
     * @return $this
     */
    public function assertJsonIsObject($key = null)
    {
        $data = $this->json($key);

        $encodedData = json_encode($data);

        PHPUnit::assertTrue(
            is_array($data)
            && str_starts_with($encodedData, '{')
            && str_ends_with($encodedData, '}')
        );

        return $this;
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @return AssertableJsonString
     *
     * @throws Throwable
     */
    public function decodeResponseJson()
    {
        $testJson = new AssertableJsonString($this->getContent());

        $decodedResponse = $testJson->json();

        if (is_null($decodedResponse) || $decodedResponse === false) {
            if ($this->exception ?? null) {
                throw $this->exception;
            }
            PHPUnit::fail('Invalid JSON was returned from the route.');
        }

        return $testJson;
    }

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param null|string $key
     */
    public function json($key = null): mixed
    {
        return $this->decodeResponseJson()->json($key);
    }

    /**
     * Get the JSON decoded body of the response as a collection.
     *
     * @param null|string $key
     */
    public function collect($key = null): Collection
    {
        return Collection::make($this->json($key));
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->json()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->json()[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    public static function fromBaseResponse(ResponseInterface $response)
    {
        return new static(new Response($response));
    }

    /**
     * Assert that the response has a successful status code.
     */
    public function assertSuccessful(): self
    {
        PHPUnit::assertTrue(
            $this->isSuccessful(),
            $this->statusMessageWithDetails('>=200, <300', $this->getStatusCode())
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param int $status
     * @return $this
     */
    public function assertStatus($status): self
    {
        $message = $this->statusMessageWithDetails($status, $actual = $this->getStatusCode());

        PHPUnit::assertSame($actual, $status, $message);

        return $this;
    }

    /**
     * Is response successful?
     *
     * @final
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }

    /**
     * Was there a server side error?
     *
     * @final
     */
    public function isServerError(): bool
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    /**
     * Assert that the response is a server error.
     */
    public function assertServerError(): self
    {
        PHPUnit::assertTrue(
            $this->isServerError(),
            $this->statusMessageWithDetails('>=500, < 600', $this->getStatusCode())
        );

        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Get the streamed content from the response.
     *
     * @return string
     */
    public function streamedContent()
    {
        if (! is_null($this->streamedContent)) {
            return $this->streamedContent;
        }

        if (! $this->response instanceof StreamedResponse) {
            PHPUnit::fail('The response is not a streamed response.');
        }

        ob_start(function (string $buffer): string {
            $this->streamedContent .= $buffer;

            return '';
        });

        $this->sendContent();

        ob_end_clean();

        return (string) $this->streamedContent;
    }

    /**
     * Sends content for the current web response.
     *
     * @return $this
     */
    public function sendContent(): static
    {
        echo $this->streamedContent;

        return $this;
    }

    /**
     * Get an assertion message for a status assertion containing extra details when available.
     *
     * @param int|string $expected
     * @param int|string $actual
     */
    protected function statusMessageWithDetails($expected, $actual): string
    {
        return "Expected response status code [{$expected}] but received {$actual}.";
    }
}
