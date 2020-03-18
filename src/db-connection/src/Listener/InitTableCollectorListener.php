<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection\Listener;

use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Register;
use Hyperf\DbConnection\Collector\Column;
use Hyperf\DbConnection\Collector\TableCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;

class InitTableCollectorListener implements ListenerInterface
{
    const DB_TO_FUNCTION = [
        'COLUMN_NAME' => 'setName',
        'ORDINAL_POSITION' => 'setPosition',
        'COLUMN_DEFAULT' => 'setDefault',
        'DATA_TYPE' => 'setType',
        'IS_NULLABLE' => 'setIsNull',
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BeforeHandle::class,
            AfterWorkerStart::class,
            BeforeProcessHandle::class,
        ];
    }

    public function process(object $event)
    {
        if ($this->container->has(ConnectionResolverInterface::class)) {
            try {
                $dbConfig = array_keys($this->container->get(ConfigInterface::class)->get('database', ['default' => 'default']));
                foreach ($dbConfig as $connectName) {
                    $this->initDatabaseTable($connectName);
                }
            } catch (\Throwable $e) {
                $this->container->get(StdoutLoggerInterface::class)->error($e->getMessage());
            }
        }
    }

    public function initDatabaseTable($connectName)
    {
        if ($this->container->get(TableCollector::class)->getDabatase($connectName)) {
            return;
        }
        $schemaTables = Register::getConnectionResolver()
            ->connection($connectName)->getSchemaBuilder()
            ->getColumn();
        $list = [];
        $schemaTables = json_decode(json_encode($schemaTables), true);
        foreach ($schemaTables as $schemaTable) {
            $tableName = $schemaTable['TABLE_NAME'];
            $schema = new Column();
            $schema->setOriginData($schemaTable);
            foreach ($schemaTable as $key => $value) {
                if ($function = self::DB_TO_FUNCTION[$key] ?? null and method_exists($schema, $function)) {
                    $schema->{$function}($value);
                }
            }
            $list[$connectName][$tableName][] = $schema;
        }
        foreach ($list as $connectName => $tableData) {
            foreach ($tableData as $tableNmame => $schema) {
                $this->container->get(TableCollector::class)->set($connectName, $tableNmame, $schema);
            }
        }
    }
}
