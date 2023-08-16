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
namespace HyperfTest\Resource;

use Hyperf\Collection\Arr;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\Stringable\Str;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Collection\data_get;

/**
 * Class HttpResponse.
 *
 * @mixin Response
 */
class HttpResponse
{
    /**
     * The response to delegate to.
     *
     * @var PsrResponseInterface
     */
    public $baseResponse;

    /**
     * Create a new test response instance.
     *
     * @param PsrResponseInterface $response
     */
    public function __construct($response)
    {
        $this->baseResponse = $response;
    }

    /**
     * Dynamically access base response parameters.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->baseResponse->{$key};
    }

    /**
     * Proxy isset() checks to the underlying base response.
     *
     * @param string $key
     * @return mixed
     */
    public function __isset($key)
    {
        return isset($this->baseResponse->{$key});
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
        return $this->baseResponse->{$method}(...$args);
    }

    /**
     * Create a new TestResponse from another response.
     *
     * @param PsrResponseInterface $response
     * @return HttpResponse
     */
    public static function fromBaseResponse($response)
    {
        return new static($response);
    }

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful()
    {
        PHPUnit::assertTrue(
            $this->isSuccessful(),
            'Response status code [' . $this->getStatusCode() . '] is not a successful status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a 200 status code.
     *
     * @return $this
     */
    public function assertOk()
    {
        PHPUnit::assertTrue(
            $this->isOk(),
            'Response status code [' . $this->getStatusCode() . '] does not match expected 200 status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a not found status code.
     *
     * @return $this
     */
    public function assertNotFound()
    {
        PHPUnit::assertTrue(
            $this->isNotFound(),
            'Response status code [' . $this->getStatusCode() . '] is not a not found status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a forbidden status code.
     *
     * @return $this
     */
    public function assertForbidden()
    {
        PHPUnit::assertTrue(
            $this->isForbidden(),
            'Response status code [' . $this->getStatusCode() . '] is not a forbidden status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param int $status
     * @return $this
     */
    public function assertStatus($status)
    {
        $actual = $this->getStatusCode();

        PHPUnit::assertTrue(
            $actual === $status,
            "Expected status code {$status} but received {$actual}."
        );

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param string $uri
     * @return $this
     */
    public function assertRedirect($uri = null)
    {
        PHPUnit::assertTrue(
            $this->isRedirect(),
            'Response status code [' . $this->getStatusCode() . '] is not a redirect status code.'
        );

        if (! is_null($uri)) {
            $this->assertLocation($uri);
        }

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param bool $strict
     * @return $this
     */
    public function assertJson(array $data, $strict = false)
    {
        PHPUnit::assertArraySubset(
            $data,
            $this->decodeResponseJson(),
            $strict,
            $this->assertJsonMessage($data)
        );

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @return $this
     */
    public function assertExactJson(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson()
        ));

        PHPUnit::assertEquals(json_encode(Arr::sortRecursive($data)), $actual);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @return $this
     */
    public function assertJsonFragment(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson()
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->jsonSearchStrings($key, $value);

            PHPUnit::assertTrue(
                Str::contains($actual, $expected),
                'Unable to find JSON fragment: ' . PHP_EOL . PHP_EOL .
                '[' . json_encode([$key => $value]) . ']' . PHP_EOL . PHP_EOL .
                'within' . PHP_EOL . PHP_EOL .
                "[{$actual}]."
            );
        }

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
        if ($exact) {
            return $this->assertJsonMissingExact($data);
        }

        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson()
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            PHPUnit::assertFalse(
                Str::contains($actual, $unexpected),
                'Found unexpected JSON fragment: ' . PHP_EOL . PHP_EOL .
                '[' . json_encode([$key => $value]) . ']' . PHP_EOL . PHP_EOL .
                'within' . PHP_EOL . PHP_EOL .
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the exact JSON fragment.
     *
     * @return $this
     */
    public function assertJsonMissingExact(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decodeResponseJson()
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            if (! Str::contains($actual, $unexpected)) {
                return $this;
            }
        }

        PHPUnit::fail(
            'Found unexpected JSON fragment: ' . PHP_EOL . PHP_EOL .
            '[' . json_encode($data) . ']' . PHP_EOL . PHP_EOL .
            'within' . PHP_EOL . PHP_EOL .
            "[{$actual}]."
        );
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param null|array $responseData
     * @return $this
     */
    public function assertJsonStructure(array $structure = null, $responseData = null)
    {
        if (is_null($structure)) {
            return $this->assertExactJson($this->json());
        }

        if (is_null($responseData)) {
            $responseData = $this->decodeResponseJson();
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                PHPUnit::assertInternalType('array', $responseData);

                foreach ($responseData as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $responseData);

                $this->assertJsonStructure($structure[$key], $responseData[$key]);
            } else {
                PHPUnit::assertArrayHasKey($value, $responseData);
            }
        }

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
        if ($key) {
            PHPUnit::assertCount(
                $count,
                data_get($this->json(), $key),
                "Failed to assert that the response count matched the expected {$count}"
            );

            return $this;
        }

        PHPUnit::assertCount(
            $count,
            $this->json(),
            "Failed to assert that the response count matched the expected {$count}"
        );

        return $this;
    }

    /**
     * Assert that the response has the given JSON validation errors for the given keys.
     *
     * @param array|string $keys
     * @return $this
     */
    public function assertJsonValidationErrors($keys)
    {
        $keys = Arr::wrap($keys);

        PHPUnit::assertNotEmpty($keys, 'No keys were provided.');

        $errors = $this->json()['errors'] ?? [];

        $errorMessage = $errors
            ? 'Response has the following JSON validation errors:' .
            PHP_EOL . PHP_EOL . json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
            : 'Response does not have JSON validation errors.';

        foreach ($keys as $key) {
            PHPUnit::assertArrayHasKey(
                $key,
                $errors,
                "Failed to find a validation error in the response for key: '{$key}'" . PHP_EOL . PHP_EOL . $errorMessage
            );
        }

        return $this;
    }

    /**
     * Assert that the response has no JSON validation errors for the given keys.
     *
     * @param array|string $keys
     * @return $this
     */
    public function assertJsonMissingValidationErrors($keys = null)
    {
        $json = $this->json();

        if (! array_key_exists('errors', $json)) {
            PHPUnit::assertArrayNotHasKey('errors', $json);

            return $this;
        }

        $errors = $json['errors'];

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
     * Validate and return the decoded response JSON.
     *
     * @param null|string $key
     * @return mixed
     */
    public function decodeResponseJson($key = null)
    {
        $decodedResponse = json_decode((string) $this->getBody(), true);

        if (is_null($decodedResponse) || $decodedResponse === false) {
            if ($this->exception) {
                throw $this->exception;
            }
            PHPUnit::fail('Invalid JSON was returned from the route.');
        }

        return data_get($decodedResponse, $key);
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @param null|string $key
     * @return mixed
     */
    public function json($key = null)
    {
        return $this->decodeResponseJson($key);
    }

    /**
     * Dump the content from the response.
     */
    public function dump()
    {
        $content = (string) $this->getBody();

        $json = json_decode($content);

        if (json_last_error() === JSON_ERROR_NONE) {
            $content = $json;
        }

        dd($content);
    }

    /**
     * Get the assertion message for assertJson.
     *
     * @return string
     */
    protected function assertJsonMessage(array $data)
    {
        $expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $actual = json_encode($this->decodeResponseJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return 'Unable to find JSON: ' . PHP_EOL . PHP_EOL .
            "[{$expected}]" . PHP_EOL . PHP_EOL .
            'within response JSON:' . PHP_EOL . PHP_EOL .
            "[{$actual}]." . PHP_EOL . PHP_EOL;
    }

    /**
     * Get the strings we need to search for when examining the JSON.
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    protected function jsonSearchStrings($key, $value)
    {
        $needle = substr(json_encode([$key => $value]), 1, -1);

        return [
            $needle . ']',
            $needle . '}',
            $needle . ',',
        ];
    }
}
