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
namespace Hyperf\Session\Handler;

use Carbon\Carbon;
use Hyperf\Collection\Arr;
use Hyperf\Database\Query\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\Support\Traits\InteractsWithTime;
use SessionHandlerInterface;

class DatabaseHandler implements SessionHandlerInterface
{
    use InteractsWithTime;

    public function __construct(protected string $connection, protected string $table, protected int $minutes)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $session = (object) $this->getQuery()->find($id);

        if (isset($session->last_activity)
            && $session->last_activity < Carbon::now()->subMinutes($this->minutes)->getTimestamp()) {
            return '';
        }

        if (isset($session->payload)) {
            return base64_decode($session->payload);
        }

        return '';
    }

    public function write(string $id, string $data): bool
    {
        $payload = $this->getDefaultPayload($data);

        if ($this->getQuery()->find($id)) {
            $this->performUpdate($id, $payload);
        } else {
            $this->performInsert($id, $payload);
        }

        return true;
    }

    public function destroy(string $id): bool
    {
        $this->getQuery()->where('id', $id)->delete();

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return $this->getQuery()
            ->where('last_activity', '<=', $this->currentTime() - $max_lifetime)
            ->delete();
    }

    /**
     * Perform an insert operation on the session ID.
     */
    protected function performInsert(string $sessionId, array $payload): bool
    {
        return $this->getQuery()->insert(Arr::set($payload, 'id', $sessionId));
    }

    /**
     * Perform an update operation on the session ID.
     */
    protected function performUpdate(string $sessionId, array $payload): int
    {
        return $this->getQuery()->where('id', $sessionId)->update($payload);
    }

    /**
     * Get the default payload for the session.
     */
    protected function getDefaultPayload(string $data): array
    {
        return [
            'payload' => base64_encode($data),
            'last_activity' => $this->currentTime(),
        ];
    }

    /**
     * Get a fresh query builder instance for the table.
     */
    protected function getQuery(): Builder
    {
        return Db::connection($this->connection)->table($this->table);
    }
}
