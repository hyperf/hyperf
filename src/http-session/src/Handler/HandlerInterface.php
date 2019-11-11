<?php
declare(strict_types=1);

namespace Hyperf\HttpSession\Handler;

/**
 * Interface HandlerInterface
 * @package Hyperf\HttpSession\Handler
 */
interface HandlerInterface {

    public function get(string $sessionId): array;

    public function delete(string $sessionId): bool;

    public function set(string $sessionId, array $sessionData, int $maxLifetime): bool;

}
