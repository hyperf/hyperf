<?php


namespace Hyperf\SocketIOServer;


use Hyperf\SocketIOServer\Room\AdapterInterface;

interface NamespaceInterface
{
    /**
     * getEventHandlers method retrieves all callbacks for any events.
     * @return array<string, callable[]>
     */
    public function getEventHandlers();

    /**
     * getNsp method retrieves a string representation of this namespace.
     */
    public function getNsp() : string;

    /**
     * getAdapter method retrieves an adapter to be used in this namespace.
     * The same adapter will not be reused in other namespace.
     */
    public function getAdapter(): AdapterInterface;
}