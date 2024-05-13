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

namespace Hyperf\Validation;

use Hyperf\Collection\Collection;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Contract\UncompromisedVerifier;

class NotPwnedVerifier implements UncompromisedVerifier
{
    /**
     * Create a new uncompromised verifier.
     * @param ClientFactory $factory the factory to create the HTTP client
     * @param int $timeout the number of seconds the request can run before timing out
     */
    public function __construct(
        protected ClientFactory $factory,
        protected int $timeout = 30,
    ) {
    }

    public function verify(array $data): bool
    {
        $value = $data['value'];
        $threshold = $data['threshold'];

        if (empty($value = (string) $value)) {
            return false;
        }

        [$hash,$hashPrefix] = $this->getHash($value);
        return ! $this->search($hashPrefix)
            ->contains(static function ($line) use ($hash, $hashPrefix, $threshold) {
                [$hashSuffix, $count] = explode(':', $line);

                return $hashPrefix . $hashSuffix === $hash && $count > $threshold;
            });
    }

    /**
     * Get the hash and its first 5 chars.
     */
    protected function getHash(string $value): array
    {
        $hash = strtoupper(sha1($value));

        $hashPrefix = substr($hash, 0, 5);

        return [$hash, $hashPrefix];
    }

    /**
     * Search by the given hash prefix and returns all occurrences of leaked passwords.
     */
    protected function search(string $hashPrefix): Collection
    {
        $client = $this->factory->create([
            'timeout' => $this->timeout,
        ]);
        $response = $client->get(
            'https://api.pwnedpasswords.com/range/' . $hashPrefix,
            [
                'headers' => [
                    'Add-Padding' => true,
                ],
            ]
        );

        $body = ($response->getStatusCode() === 200)
            ? $response->getBody()->getContents()
            : '';

        return Str::of($body)->trim()->explode("\n")->filter(function ($line) {
            return str_contains($line, ':');
        });
    }
}
