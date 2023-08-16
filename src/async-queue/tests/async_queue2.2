#!/usr/bin/env php
<?php
declare(strict_types=1);

namespace HyperfTest\AsyncQueue\Stub {
    require_once __DIR__ . '/../../contract/src/UnCompressInterface.php';
    require_once __DIR__ . '/../../contract/src/CompressInterface.php';
    require_once __DIR__ . '/../src/MessageInterface.php';
    require_once __DIR__ . '/../src/JobInterface.php';
    require_once __DIR__ . '/../src/Job.php';

    use Hyperf\AsyncQueue\Job;

    class DemoJob extends Job
    {
        public $id;

        public $model;

        protected int $maxAttempts = 1;

        public function __construct($id, $model = null)
        {
            $this->id = $id;
            $this->model = $model;
        }

        public function handle()
        {
        }
    }
}

namespace Hyperf\AsyncQueue {
    use Hyperf\Contract\CompressInterface;
    use Hyperf\Contract\UnCompressInterface;
    use Serializable;

    class Message implements MessageInterface, Serializable
    {
        /**
         * @var CompressInterface|JobInterface|UnCompressInterface
         */
        protected $job;

        /**
         * @var int
         */
        protected $attempts = 0;

        public function __construct(JobInterface $job)
        {
            $this->job = $job;
        }

        public function job(): JobInterface
        {
            return $this->job;
        }

        public function attempts(): bool
        {
            if ($this->job->getMaxAttempts() > $this->attempts++) {
                return true;
            }
            return false;
        }

        public function getAttempts(): int
        {
            return $this->attempts;
        }

        public function serialize()
        {
            if ($this->job instanceof CompressInterface) {
                $this->job = $this->job->compress();
            }

            return serialize([$this->job, $this->attempts]);
        }

        public function unserialize($serialized)
        {
            [$job, $attempts] = unserialize($serialized);
            if ($job instanceof UnCompressInterface) {
                $job = $job->uncompress();
            }

            $this->job = $job;
            $this->attempts = $attempts;
        }
    }

    $message = new \Hyperf\AsyncQueue\Message(
        new \HyperfTest\AsyncQueue\Stub\DemoJob(9501)
    );

    file_put_contents(__DIR__ . '/message2.2.cache', serialize($message));
}
