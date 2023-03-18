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
namespace Hyperf\Swagger\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Database\Model\Model;
use Hyperf\Swagger\Command\Ast\ModelSchemaVisitor;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Symfony\Component\Console\Input\InputOption;

class GenSchemaCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('gen:swagger-schema');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generate swagger schemas.');
        $this->addOption('name', 'N', InputOption::VALUE_OPTIONAL, 'The schema name.');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Whether to force generate the schema.');
        $this->addOption('model', 'M', InputOption::VALUE_OPTIONAL, 'The model which used to generate schemas.');
    }

    public function handle()
    {
        $name = $this->input->getOption('name');
        $force = $this->input->getOption('force');
        $model = $this->input->getOption('model');

        if ($model) {
            if (! class_exists($model)) {
                $this->output->error(sprintf('The model %s is not exists.', $model));
                return;
            }
            $ref = new ReflectionClass($model);
            /** @var Model $model */
            $model = new $model();
        }

        if (! $name) {
            if (! $model) {
                $this->output->error('The one of name or model must be exists.');
                return;
            }

            $name = $ref->getShortName() . 'Schema';
        }

        $path = BASE_PATH . '/app/Schema/' . $name . '.php';
        if (file_exists($path) && ! $force) {
            $this->output->error(sprintf('The path of schema %s is exists.', $path));
            return;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/schema.stub');

        $code = str_replace('%NAME', $name, $stub);

        if (! $model) {
            file_put_contents($path, $code);
            return;
        }

        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7, $lexer);
        $printer = new Standard();

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new ModelSchemaVisitor($ref, $model));

        $stmts = $traverser->traverse($parser->parse($code));

        file_put_contents($path, '<?php' . PHP_EOL . $printer->prettyPrint($stmts));
    }
}
