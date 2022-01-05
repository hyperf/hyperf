<?php

namespace Hyperf\Database\DBAL;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Hyperf\Database\DBAL\Concerns\ConnectsToDatabase;

class PostgresDriver extends AbstractPostgreSQLDriver
{
    use ConnectsToDatabase;
}
