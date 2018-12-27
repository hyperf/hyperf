<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Aop;

use PhpParser\Parser as ParserInterface;
use PhpParser\ParserFactory;

class AstParserFactory
{
    public function __invoke(): ParserInterface
    {
        $parserFactory = new ParserFactory();
        return $parserFactory->create(ParserFactory::ONLY_PHP7);
    }
}
