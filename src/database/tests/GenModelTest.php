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

use Hyperf\Database\Commands\Ast\ModelUpdateVisitor;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Schema\MySqlBuilder;
use HyperfTest\Database\Stubs\ContainerStub;
use HyperfTest\Database\Stubs\Model\UserExtEmpty;
use Mockery;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class GenModelTest extends TestCase
{
    protected $license = '<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */';

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testDatabaseFormat()
    {
        $container = ContainerStub::getContainer();
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturnUsing(function () {
            $dispatcher = Mockery::mock(EventDispatcherInterface::class);
            $dispatcher->shouldReceive('dispatch')->withAnyArgs()->andReturn(null);
            return $dispatcher;
        });
        $connection = $container->get(ConnectionResolverInterface::class)->connection();
        /** @var MySqlBuilder $builder */
        $builder = $connection->getSchemaBuilder('default');
        $columns = $this->formatColumns($builder->getColumnTypeListing('user_ext'));
        foreach ($columns as $i => $column) {
            if ($column['column_name'] === 'created_at') {
                $columns[$i]['cast'] = 'datetime';
            }
        }

        $astParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $stms = $astParser->parse(file_get_contents(__DIR__ . '/Stubs/Model/UserExtEmpty.php'));
        $traverser = new NodeTraverser();
        $visitor = new ModelUpdateVisitor(UserExtEmpty::class, $columns, ContainerStub::getModelOption());
        $traverser->addVisitor($visitor);
        $stms = $traverser->traverse($stms);
        $code = (new Standard())->prettyPrintFile($stms);
        $this->assertEquals($this->license . '
namespace HyperfTest\Database\Stubs\Model;

/**
 * @property int $id 
 * @property int $count 
 * @property string $float_num 
 * @property string $str 
 * @property string $json 
 * @property \Carbon\Carbon $created_at 
 * @property string $updated_at 
 */
class UserExtEmpty extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = \'user_ext\';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [\'id\', \'count\', \'float_num\', \'str\', \'json\', \'created_at\', \'updated_at\'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [\'id\' => \'integer\', \'count\' => \'integer\', \'created_at\' => \'datetime\'];
}', $code);
    }

    /**
     * Format column's key to lower case.
     */
    protected function formatColumns(array $columns): array
    {
        return array_map(function ($item) {
            return array_change_key_case($item, CASE_LOWER);
        }, $columns);
    }
}
