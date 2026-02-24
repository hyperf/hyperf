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

namespace HyperfTest\Validation\Cases;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Model\Model;
use Hyperf\Validation\Rules\Unique;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ValidationUniqueRuleTest extends TestCase
{
    protected function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('make')
            ->with(DatabaseModelWithConnection::class)
            ->andReturn(new DatabaseModelWithConnection());

        ApplicationContext::setContainer($container);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testItCorrectlyFormatsAStringVersionOfTheRule()
    {
        $rule = new Unique('table');
        $rule->where('foo', 'bar');
        $this->assertEquals('unique:table,NULL,NULL,id,foo,"bar"', (string) $rule);

        $rule = new Unique('table', 'column');
        $rule->ignore('Taylor, Otwell', 'id_column');
        $rule->where('foo', 'bar');
        $this->assertEquals('unique:table,column,"Taylor, Otwell",id_column,foo,"bar"', (string) $rule);

        $rule = new Unique('table', 'column');
        $rule->ignore('Taylor, Otwell"\'..-"', 'id_column');
        $rule->where('foo', 'bar');
        $this->assertEquals('unique:table,column,"Taylor, Otwell\"\\\'..-\"",id_column,foo,"bar"', (string) $rule);
        $this->assertEquals('Taylor, Otwell"\'..-"', stripslashes(str_getcsv('table,column,"Taylor, Otwell\"\\\'..-\"",id_column,foo,"bar"')[2]));
        $this->assertEquals('id_column', stripslashes(str_getcsv('table,column,"Taylor, Otwell\"\\\'..-\"",id_column,foo,"bar"')[3]));

        $rule = new Unique('table', 'column');
        $rule->ignore(null, 'id_column');
        $rule->where('foo', 'bar');
        $this->assertEquals('unique:table,column,NULL,id_column,foo,"bar"', (string) $rule);

        $model = new DatabaseModelStub(['id_column' => 1]);

        $rule = new Unique('table', 'column');
        $rule->ignore($model);
        $rule->where('foo', 'bar');
        $this->assertEquals('unique:table,column,"1",id_column,foo,"bar"', (string) $rule);

        $rule = new Unique('table', 'column');
        $rule->ignore($model, 'id_column');
        $rule->where('foo', 'bar');
        $this->assertEquals('unique:table,column,"1",id_column,foo,"bar"', (string) $rule);

        $rule = new Unique('table');
        $rule->where('foo', '"bar"');
        $this->assertEquals('unique:table,NULL,NULL,id,foo,"""bar"""', (string) $rule);

        $rule = new Unique('table');
        $rule->where('foo', 1);
        $this->assertEquals('unique:table,NULL,NULL,id,foo,"1"', (string) $rule);

        $rule = new Unique(DatabaseModelWithConnection::class, 'column');
        $rule->where('foo', 'bar');
        $this->assertSame('unique:mysql.table,column,NULL,id,foo,"bar"', (string) $rule);
    }
}

class DatabaseModelStub extends Model
{
    protected string $primaryKey = 'id_column';

    protected array $guarded = [];
}

class DatabaseModelWithConnection extends DatabaseModelStub
{
    protected ?string $table = 'table';

    protected ?string $connection = 'mysql';
}
