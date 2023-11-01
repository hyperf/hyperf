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

use Hyperf\Database\Commands\Ast\GenerateModelIDEVisitor;
use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use HyperfTest\Database\Stubs\Model\TestGenerateIdeModel;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ModelGenerateTest extends TestCase
{
    public function setUp(): void
    {
        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $this->astParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7, $this->lexer);
        $this->printer = new Standard();
    }

    public function testGenerateScope()
    {
        $code = file_get_contents(__DIR__ . '/Stubs/Model/TestGenerateIdeModel.php');

        $option = new ModelOption();
        $data = new ModelData(TestGenerateIdeModel::class, [
            ['column_name' => 'name'],
            ['column_name' => 'age'],
        ]);

        $stmts = $this->astParser->parse($code);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new GenerateModelIDEVisitor($option, $data));
        $stmts = $traverser->traverse($stmts);
        $code = $this->printer->prettyPrintFile($stmts);

        $this->assertNotFalse(strpos($code, 'public static function optionNull(?string $test)'));
        $this->assertNotFalse(strpos($code, 'public static function string(string $test)'));
        $this->assertNotFalse(strpos($code, 'public static function union(int $appId, string|int $uid)'));
        $this->assertNotFalse(strpos($code, 'public static function unionOrNull(int $appId, string|int|null $uid)'));
        $this->assertNotFalse(strpos($code, 'public static function singleOrNull(?string $test)'));
    }
}
