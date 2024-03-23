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

namespace Hyperf\Nacos\Protobuf\Response;

class ConfigQueryResponse extends Response
{
    protected string $content;

    protected string $encryptedDataKey;

    protected string $contentType;

    protected string $md5;

    protected int $lastModified;

    protected bool $beta;

    public function __construct(array $json)
    {
        $this->content = $json['content'];
        $this->encryptedDataKey = $json['encryptedDataKey'];
        $this->contentType = $json['contentType'];
        $this->md5 = $json['md5'];
        $this->lastModified = $json['lastModified'];
        $this->beta = $json['beta'];

        parent::__construct(...parent::namedParameters($json));
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getEncryptedDataKey(): string
    {
        return $this->encryptedDataKey;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getMd5(): string
    {
        return $this->md5;
    }

    public function getLastModified(): int
    {
        return $this->lastModified;
    }

    public function isBeta(): bool
    {
        return $this->beta;
    }
}
