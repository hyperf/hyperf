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
use Hyperf\Database\Query\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Arr;
use Hyperf\Utils\InteractsWithTime;
use SessionHandlerInterface;

class DatabaseHandler implements SessionHandlerInterface
{
    use InteractsWithTime;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var int
     */
    protected $minutes;

    public function __construct(string $connection, string $table, int $minutes)
    {
        $this->table = $table;
        $this->minutes = $minutes;
        $this->connection = $connection;
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        $session = (object) $this->getQuery()->find($sessionId);

        if (isset($session->last_activity)
            && $session->last_activity < Carbon::now()->subMinutes($this->minutes)->getTimestamp()) {
            return '';
        }

        if (isset($session->payload)) {
            return base64_decode($session->payload);
        }

        return '';
    }

    public function write($sessionId, $data)
    {
        $payload = $this->getDefaultPayload($data);

        if ($this->getQuery()->find($sessionId)) {
            $this->performUpdate($sessionId, $payload);
        } else {
            $this->performInsert($sessionId, $payload);
        }

        return true;
    }

    public function destroy($sessionId)
    {
        $this->getQuery()->where('id', $sessionId)->delete();

        return true;
    }

    public function gc($lifetime)
    {
        return (bool) $this->getQuery()
            ->where('last_activity', '<=', $this->currentTime() - $lifetime)
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
     *
     * @param string $data
     */
    protected function getDefaultPayload($data): array
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
