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
namespace HyperfTest\Database;

use Hyperf\Database\Model\Relations\Relation;
use HyperfTest\Database\Stubs\Model\Book;
use HyperfTest\Database\Stubs\Model\User;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ModelMorphEagerLoadingTest extends TestCase
{
    protected function setUp()
    {
        Relation::morphMap([
            'user' => User::class,
            'book' => Book::class,
        ]);
    }

    protected function tearDown()
    {
        Mockery::close();
        Relation::$morphMap = [];
    }

    public function testExample()
    {
        $this->assertTrue(true);
    }
}
