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
namespace Hyperf\Nsq;

use Hyperf\CodeParser\Package;

use function Hyperf\Support\value;

/**
 * NSQ Protocol https://nsq.io/clients/tcp_protocol_spec.html.
 */
class MessageBuilder
{
    /**
     * After connecting, a client must send a 4-byte “magic” identifier
     * indicating what version of the protocol they will be communicating.
     */
    public function buildMagic(): string
    {
        return '  V2';
    }

    /**
     * Publish a message to a topic
     * Success Response: OK
     * Error Responses: E_INVALID
     *                  E_BAD_TOPIC
     *                  E_BAD_MESSAGE
     *                  E_PUB_FAILED.
     */
    public function buildPub(string $topic, string $message): string
    {
        $command = "PUB {$topic}\n";
        $size = Packer::packUInt32(strlen($message));

        return $command . $size . $message;
    }

    /**
     * Publish multiple messages to a topic
     * Success Response: OK
     * Error Responses: E_INVALID
     *                  E_BAD_TOPIC
     *                  E_BAD_BODY
     *                  E_BAD_MESSAGE
     *                  E_MPUB_FAILED.
     */
    public function buildMPub(string $topic, array $messages): string
    {
        $command = "MPUB {$topic}\n";
        $numMessages = Packer::packUInt32(count($messages));
        $packedMessage = '';
        foreach ($messages as $message) {
            $packedMessage .= Packer::packUInt32(strlen($message)) . $message;
        }
        $size = Packer::packUInt32(strlen($numMessages . $packedMessage));

        return $command . $size . $numMessages . $packedMessage;
    }

    /**
     * Publish a deferred message to a topic
     * Success Response: OK
     * Error Responses: E_INVALID
     *                  E_BAD_TOPIC
     *                  E_BAD_MESSAGE
     *                  E_DPUB_FAILED.
     */
    public function buildDPub(string $topic, string $message, int $deferTime = 0): string
    {
        $command = "DPUB {$topic} {$deferTime}\n";
        $size = Packer::packUInt32(strlen($message));
        return $command . $size . $message;
    }

    public function buildAuth(string $identity): string
    {
        $command = "AUTH\n";
        $size = Packer::packUInt32(strlen($identity));
        return $command . $size . $identity;
    }

    /**
     * Subscribe to a topic/channel
     * Success Response: OK
     * Error Responses: E_INVALID
     *                  E_BAD_TOPIC
     *                  E_BAD_CHANNEL.
     */
    public function buildSub(string $topic, string $channel): string
    {
        return "SUB {$topic} {$channel}\n";
    }

    /**
     * Update RDY state (indicate you are ready to receive N messages)
     * There is no success response.
     * Error Responses: E_INVALID.
     */
    public function buildRdy(int $count): string
    {
        return "RDY {$count}\n";
    }

    /**
     * Reset the timeout for an in-flight message.
     * There is no success response.
     * Error Responses: E_INVALID
     *                  E_TOUCH_FAILED.
     */
    public function buildTouch(string $id): string
    {
        return "TOUCH {$id}\n";
    }

    /**
     * Finish a message (indicate successful processing).
     * There is no success response.
     * Error Responses: E_INVALID
     *                  E_FIN_FAILED.
     */
    public function buildFin(string $id): string
    {
        return "FIN {$id}\n";
    }

    /**
     * Re-queue a message (indicate failure to process)
     * There is no success response.
     * Error Responses: E_INVALID
     *                  E_REQ_FAILED.
     */
    public function buildReq(string $id, int $timeout = 1): string
    {
        return "REQ {$id} {$timeout}\n";
    }

    /**
     * No-op.
     * There is no response.
     */
    public function buildNop(): string
    {
        return "NOP\n";
    }

    /**
     * Cleanly close your connection (no more messages are sent).
     * Success Responses: CLOSE_WAIT
     * Error Responses: E_INVALID.
     */
    public function buildCls(): string
    {
        return "CLS\n";
    }

    public function buildIdentify(): string
    {
        $command = "IDENTIFY\n";
        $version = Package::getPrettyVersion('hyperf/nsq');
        $hostname = value(function () {
            $hostname = gethostname();
            if (! is_string($hostname)) {
                return 'consumer-' . rand(0, 9999);
            }

            return gethostbyname($hostname) ?: 'unknown';
        });
        $message = json_encode([
            'hostname' => $hostname,
            'user_agent' => 'hyperf-nsq/' . $version,
            'feature_negotiation' => true,
        ]);
        $size = Packer::packUInt32(strlen($message));
        return $command . $size . $message;
    }
}
