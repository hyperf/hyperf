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

/**
 * Message Class.
 */
class Message
{
    /**
     * Message Body.
     *
     * @var string
     */
    public $body;

    /**
     * Message Subject.
     *
     * @var string
     */
    private $subject;

    /**
     * Message Ssid.
     *
     * @var string
     */
    private $sid;

    /**
     * Message related connection.
     *
     * @var Connection
     */
    private $conn;

    /**
     * Message constructor.
     *
     * @param string $subject message subject
     * @param string $body message body
     * @param string $sid message Sid
     * @param Connection $conn message Connection
     */
    public function __construct($subject, $body, $sid, Connection $conn)
    {
        $this->setSubject($subject);
        $this->setBody($body);
        $this->setSid($sid);
        $this->setConn($conn);
    }

    /**
     * String representation of a message.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getBody();
    }

    /**
     * Set subject.
     *
     * @param string $subject subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body.
     *
     * @param string $body body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody()
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
    public function setSid($sid)
    {
        $this->sid = $sid;
        return $this;
    }

    /**
     * Get Ssid.
     *
     * @return string
     */
    public function getSid()
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
    public function setConn(Connection $conn)
    {
        $this->conn = $conn;
        return $this;
    }

    /**
     * Get Conn.
     *
     * @return Connection
     */
    public function getConn()
    {
        return $this->conn;
    }

    /**
     * Allows you reply the message with a specific body.
     *
     * @param string $body body to be set
     */
    public function reply($body)
    {
        $this->conn->publish(
            $this->subject,
            $body
        );
    }
}
