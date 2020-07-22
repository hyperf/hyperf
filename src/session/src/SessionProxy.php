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
namespace Hyperf\Session;

use Hyperf\Contract\SessionInterface;
use Hyperf\Utils\Context;

class SessionProxy extends Session
{
    public function __construct()
    {
        // Do nothing
    }

    public function flash(string $key, $value = true): void
    {
        $this->getSession()->flash($key, $value);
    }

    public function now(string $key, $value): void
    {
        $this->getSession()->now($key, $value);
    }

    public function reflash(): void
    {
        $this->getSession()->reflash();
    }

    public function keep($keys = null): void
    {
        $this->getSession()->keep($keys);
    }

    public function flashInput(array $value): void
    {
        $this->getSession()->flashInput($value);
    }

    public function ageFlashData(): void
    {
        $this->getSession()->ageFlashData();
    }

    public function isValidId(string $id): bool
    {
        return $this->getSession()->isValidId($id);
    }

    public function start(): bool
    {
        return $this->getSession()->start();
    }

    public function getId(): string
    {
        return $this->getSession()->getId();
    }

    public function setId(string $id): void
    {
        $this->getSession()->setId($id);
    }

    public function getName(): string
    {
        return $this->getSession()->getName();
    }

    public function setName(string $name): void
    {
        $this->getSession()->setName($name);
    }

    public function invalidate(?int $lifetime = null): bool
    {
        return $this->getSession()->invalidate($lifetime);
    }

    public function migrate(bool $destroy = false, ?int $lifetime = null): bool
    {
        return $this->getSession()->migrate($destroy, $lifetime);
    }

    public function save(): void
    {
        $this->getSession()->save();
    }

    public function has(string $name): bool
    {
        return $this->getSession()->has($name);
    }

    public function get(string $name, $default = null)
    {
        return $this->getSession()->get($name, $default);
    }

    public function set(string $name, $value): void
    {
        $this->getSession()->set($name, $value);
    }

    public function put($key, $value = null): void
    {
        $this->getSession()->put($key, $value);
    }

    public function all(): array
    {
        return $this->getSession()->all();
    }

    public function replace(array $attributes): void
    {
        $this->getSession()->replace($attributes);
    }

    public function remove(string $name)
    {
        return $this->getSession()->remove($name);
    }

    public function forget($keys): void
    {
        $this->getSession()->forget($keys);
    }

    public function clear(): void
    {
        $this->getSession()->clear();
    }

    public function isStarted(): bool
    {
        return $this->getSession()->isStarted();
    }

    public function token(): string
    {
        return $this->getSession()->token();
    }

    public function regenerateToken(): string
    {
        return $this->getSession()->regenerateToken();
    }

    public function previousUrl(): ?string
    {
        return $this->getSession()->previousUrl();
    }

    public function setPreviousUrl(string $url): void
    {
        $this->getSession()->setPreviousUrl($url);
    }

    public function push(string $key, $value): void
    {
        $this->getSession()->push($key, $value);
    }

    protected function getSession(): Session
    {
        return Context::get(SessionInterface::class);
    }

    protected function mergeNewFlashes(array $keys): void
    {
        $this->getSession()->mergeNewFlashes($keys);
    }

    protected function removeFromOldFlashData(array $keys): void
    {
        $this->getSession()->removeFromOldFlashData($keys);
    }

    protected function generateSessionId(): string
    {
        return $this->getSession()->generateSessionId();
    }

    protected function loadSession(): void
    {
        $this->getSession()->loadSession();
    }

    protected function readFromHandler(): array
    {
        return $this->getSession()->readFromHandler();
    }

    protected function prepareForUnserialize(string $data): string
    {
        return $this->getSession()->prepareForUnserialize($data);
    }

    protected function prepareForStorage(string $data): string
    {
        return $this->getSession()->prepareForStorage($data);
    }
}
