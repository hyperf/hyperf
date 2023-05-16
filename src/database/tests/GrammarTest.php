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
namespace HyperfTest\Database;

use Hyperf\Database\MySqlConnection;
use Hyperf\Database\Query\Grammars\MySqlGrammar as MySqlQueryGrammar;
use Hyperf\Database\Schema\Grammars\MySqlGrammar;
use Hyperf\Support\Fluent;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class GrammarTest extends TestCase
{
    public function testWrap()
    {
        $grammar = new MySqlGrammar();
        $this->assertSame('`user`', $grammar->wrap('user'));
        $this->assertSame('`book`', $grammar->wrap(new Fluent(['name' => 'book'])));
    }

    public function testGetDefaultQueryGrammar()
    {
        $conn = new MySqlConnection(fn () => 1);
        $grammar = $conn->getQueryGrammar();
        $this->assertInstanceOf(MySqlQueryGrammar::class, $grammar);
    }
}
