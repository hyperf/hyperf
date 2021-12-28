<?php

namespace Hyperf\Session\Handler;

use Carbon\Carbon;
use Hyperf\Utils\Arr;
use Hyperf\DbConnection\Db;
use SessionHandlerInterface;
use Hyperf\Utils\InteractsWithTime;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Exception\QueryException;

class DatabaseHandler implements SessionHandlerInterface
{
    use InteractsWithTime;

    protected $connection;

    protected $table;

    protected $minutes;

    public function __construct($connection, $table, $minutes)
    {
        $this->table = $table;
        $this->minutes = $minutes;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $session = (object) $this->getQuery()->find($sessionId);

        if (isset($session->last_activity) &&
            $session->last_activity < Carbon::now()->subMinutes($this->minutes)->getTimestamp()) {
            return '';
        }

        if (isset($session->payload)) {
            return base64_decode($session->payload);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * Perform an insert operation on the session ID.
     *
     * @param  string  $sessionId
     * @param  string  $payload
     * @return bool|null
     */
    protected function performInsert($sessionId, $payload)
    {
        //try {
            return $this->getQuery()->insert(Arr::set($payload, 'id', $sessionId));
        //} catch (QueryException $e) {
        //    $this->performUpdate($sessionId, $payload);
        //}
    }

    /**
     * Perform an update operation on the session ID.
     *
     * @param  string  $sessionId
     * @param  string  $payload
     * @return int
     */
    protected function performUpdate($sessionId, $payload)
    {
        return $this->getQuery()->where('id', $sessionId)->update($payload);
    }

    /**
     * Get the default payload for the session.
     *
     * @param  string  $data
     * @return array
     */
    protected function getDefaultPayload($data)
    {
        $payload = [
            'payload' => base64_encode($data),
            'last_activity' => $this->currentTime(),
        ];

        return $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->getQuery()->where('id', $sessionId)->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        $this->getQuery()->where('last_activity', '<=', $this->currentTime() - $lifetime)->delete();
    }

    /**
     * Get a fresh query builder instance for the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        //return $this->connection->table($this->table);
        return Db::connection($this->connection)->table($this->table);
    }
}
