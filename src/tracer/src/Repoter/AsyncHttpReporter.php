<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer;

use Hyperf\Task\Annotation\Task;
use Psr\Log\LoggerInterface;
use Zipkin\Recording\Span as MutableSpan;
use Zipkin\Reporter;
use Zipkin\Reporters\Http;
use Zipkin\Reporters\Http\ClientFactory;

class AsyncHttpReporter implements Reporter
{
    /**
     * @var null|ClientFactory
     */
    private $requesterFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    public function __construct(
        ?ClientFactory $requesterFactory = null,
        array $options = [],
        ?LoggerInterface $logger = null
    ) {
        $this->requesterFactory = $requesterFactory;
        $this->options = $options;
        $this->logger = $logger;
    }

    /**
     * @param MutableSpan[] $spans
     * @return void
     * @Task
     */
    public function report(array $spans): void
    {
        $reporter = new Http($this->requesterFactory, $this->options, $this->logger);
        $reporter->report($spans);
    }
}
