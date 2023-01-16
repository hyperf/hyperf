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
namespace Hyperf\Nats;

use Stringable;

/**
 * Message Class.
 */
class Message implements Stringable
{
    /**
     * Message Body.
     */
    public mixed $body;

    /**
     * Message Subject.
     */
    private string $subject;

    /**
     * Message Ssid.
     */
    private string $sid;

    /**
     * Message related connection.
     */
    private Connection $conn;

    /**
     * Message constructor.
     *
     * @param string $subject message subject
     * @param mixed $body message body
     * @param string $sid message Sid
     * @param Connection $conn message Connection
     */
    public function __construct(string $subject, mixed $body, string $sid, Connection $conn)
    {
        $this->setSubject($subject);
        $this->setBody($body);
        $this->setSid($sid);
        $this->setConn($conn);
    }

    /**
     * String representation of a message.
     */
    public function __toString(): string
    {
        return (string) $this->getBody();
    }

    /**
     * Set subject.
     *
     * @param string $subject subject
     *
     * @return $this
     */
    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Set body.
     *
     * @param mixed $body body
     *
     * @return $this
     */
    public function setBody(mixed $body): static
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get body.
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * Set Ssid.
     *
     * @param string $sid ssid
     *
     * @return $this
     */
    public function setSid(string $sid): static
    {
        $this->sid = $sid;
        return $this;
    }

    /**
     * Get Ssid.
     */
    public function getSid(): string
    {
        return $this->sid;
    }

    /**
     * Set Conn.
     *
     * @param Connection $conn connection
     *
     * @return $this
     */
    public function setConn(Connection $conn): static
    {
        $this->conn = $conn;
        return $this;
    }

    /**
     * Get Conn.
     */
    public function getConn(): Connection
    {
        return $this->conn;
    }

    /**
     * Allows you reply the message with a specific body.
     *
     * @param string $body body to be set
     */
    public function reply(string $body)
    {
        $this->conn->publish(
            $this->subject,
            $body
        );
    }
}
