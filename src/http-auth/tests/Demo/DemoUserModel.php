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

namespace HyperfTest\Demo;

use Hyperf\HttpAuth\Contract\Authenticatable;

class DemoUserModel implements Authenticatable
{
    use \Hyperf\HttpAuth\Authenticatable;

    public $id = 1;

    public $username = 'administrator';

    public $email = 'admin@gmail.com';

    public $password = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm'; // secret

    public $remember_token;

    public function getKeyName()
    {
        return 'id';
    }
}
