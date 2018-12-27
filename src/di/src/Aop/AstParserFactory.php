<?php

namespace Hyperflex\Di\Aop;

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