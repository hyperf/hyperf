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

namespace Hyperf\Rpn;

use Hyperf\Rpn\Exception\InvalidExpressionException;
use Hyperf\Rpn\Exception\InvalidOperatorException;
use Hyperf\Rpn\Exception\NotFoundException;
use Hyperf\Rpn\Operator\AddOperator;
use Hyperf\Rpn\Operator\DivideOperator;
use Hyperf\Rpn\Operator\HasBindings;
use Hyperf\Rpn\Operator\MultiplyOperator;
use Hyperf\Rpn\Operator\OperatorInterface;
use Hyperf\Rpn\Operator\SubtractOperator;
use SplQueue;
use SplStack;

class Calculator
{
    use HasBindings;

    /**
     * @var OperatorInterface[]
     */
    protected array $operators = [];

    /**
     * @param OperatorInterface[] $operators
     */
    public function __construct(array $operators = [])
    {
        $operators = array_merge($this->getDefaultOperators(), $operators);
        foreach ($operators as $operator) {
            if (! $operator instanceof OperatorInterface) {
                throw new InvalidOperatorException(sprintf('%s is not instanceof %s.', get_class($operator), OperatorInterface::class));
            }
            $this->operators[$operator->getOperator()] = $operator;
        }
    }

    public function calculate(string $expression, array $bindings = [], int $scale = 0): string
    {
        $queue = new SplQueue();
        $tags = explode(' ', $expression);
        foreach ($tags as $tag) {
            if (! $this->isOperator($tag)) {
                $queue->push($tag);
                continue;
            }

            $operator = $this->getOperator($tag);

            $params = [];
            $length = $operator->length();
            while (true) {
                $value = $queue->pop();

                if ($length === null && $this->isOperator($value)) {
                    $queue->push($value);
                    break;
                }

                $params[] = $value;
                --$length;
                if ($length <= 0) {
                    break;
                }
            }

            $queue->push($operator->execute(array_reverse($params), $scale, $bindings));
        }

        if ($queue->count() !== 1) {
            throw new InvalidExpressionException(sprintf('The expression %s is invalid.', $expression));
        }

        return $queue->pop();
    }

    public function toRPNExpression(string $expression): string
    {
        $numStack = new SplStack();
        $operaStack = new SplStack();
        preg_match_all('/((?:[0-9\.]+)|(?:[\(\)\+\-\*\/])){1}/', $expression, $matches);
        foreach ($matches[0] as $key => &$match) {
            if (is_numeric($match)) {
                $numStack->push($match);
                continue;
            }
            if ($match === '(') {
                $operaStack->push($match);
                continue;
            }
            if ($match === ')') {
                $_tag = $operaStack->pop();
                while ($_tag !== '(') {
                    $numStack->push($_tag);
                    $_tag = $operaStack->pop();
                }
                continue;
            }
            if ($match === '-' && ($key === 0 || (isset($matches[0][$key - 1]) && in_array($matches[0][$key - 1], ['+', '-', '*', '/', '('])))) {
                $numStack->push($match . $matches[0][$key + 1]);
                unset($matches[0][$key + 1]);
                continue;
            }
            if (in_array($match, ['-', '+'])) {
                while (! $operaStack->isEmpty() && in_array($operaStack->top(), ['+', '-', '*', '/'])) {
                    $numStack->push($operaStack->pop());
                }
            }
            $operaStack->push($match);
        }
        while (! $operaStack->isEmpty()) {
            $numStack->push($operaStack->pop());
        }
        $rpnExp = '';
        while (! $numStack->isEmpty()) {
            $rpnExp .= ($numStack->shift() . ' ');
        }
        return trim($rpnExp);
    }

    protected function getOperator(string $tag): OperatorInterface
    {
        $operator = $this->operators[$tag] ?? null;
        if (! $operator instanceof OperatorInterface) {
            throw new NotFoundException(sprintf('Operator %s is not found.', $tag));
        }

        return $operator;
    }

    protected function isOperator(string $tag): bool
    {
        return array_key_exists($tag, $this->operators);
    }

    protected function getDefaultOperators(): array
    {
        return [
            new AddOperator(),
            new SubtractOperator(),
            new MultiplyOperator(),
            new DivideOperator(),
        ];
    }
}
