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
     * getNamespace method retrieves a string representation of this namespace.
     */
    public function getNamespace(): string;

    /**
     * getAdapter method retrieves an adapter to be used in this namespace.
     * The same adapter will not be reused in other namespace.
     */
    public function getAdapter(): AdapterInterface;
}
