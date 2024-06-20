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
 * Class ServerInfo.
 */
class ServerInfo
{
    /**
     * Server unique ID.
     */
    private string $serverID;

    /**
     * Server hostname.
     */
    private string $host;

    /**
     * Server port.
     */
    private int $port;

    /**
     * Server version number.
     */
    private string $version;

    /**
     * Server Golang version.
     */
    private string $goVersion;

    /**
     * Is authorization required?
     */
    private bool $authRequired;

    /**
     * Is TLS required?
     */
    private bool $TLSRequired;

    /**
     * Should TLS be verified?
     */
    private bool $TLSVerify;

    /**
     * Is SSL required?
     */
    private bool $SSLRequired;

    /**
     * Max payload size.
     */
    private int $maxPayload;

    /**
     * Connection URL list.
     */
    private array $connectURLs;

    /**
     * ServerInfo constructor.
     *
     * @param string $connectionResponse connection response Message
     */
    public function __construct($connectionResponse)
    {
        $parts = explode(' ', $connectionResponse);
        $data = json_decode($parts[1], true);

        $this->setServerID($data['server_id']);
        $this->setHost($data['host']);
        $this->setPort($data['port']);
        $this->setVersion($data['version']);
        $this->setGoVersion($data['go']);
        $this->setAuthRequired($data['auth_required'] ?? false);
        $this->setTLSRequired($data['tls_required'] ?? false);
        $this->setTLSVerify($data['tls_verify'] ?? false);
        $this->setMaxPayload($data['max_payload']);

        if (version_compare($data['version'], '1.1.0') === -1) {
            $this->setSSLRequired($data['ssl_required']);
        }
    }

    /**
     * Get the server ID.
     *
     * @return string server ID
     */
    public function getServerID(): string
    {
        return $this->serverID;
    }

    /**
     * Set the server ID.
     *
     * @param string $serverID server ID
     */
    public function setServerID(string $serverID): void
    {
        $this->serverID = $serverID;
    }

    /**
     * Get the server host name or ip.
     *
     * @return string server host
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set the server host name or ip.
     *
     * @param string $host server host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * Get server port number.
     *
     * @return int server port number
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set server port number.
     *
     * @param int $port server port number
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * Get server version number.
     *
     * @return string server version number
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set server version number.
     *
     * @param string $version server version number
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Get the golang version number.
     *
     * @return string go version number
     */
    public function getGoVersion(): string
    {
        return $this->goVersion;
    }

    /**
     * Set the golang version number.
     *
     * @param string $goVersion go version number
     */
    public function setGoVersion(string $goVersion): void
    {
        $this->goVersion = $goVersion;
    }

    /**
     * Check if server requires authorization.
     *
     * @return bool if auth is required
     */
    public function isAuthRequired(): bool
    {
        return $this->authRequired;
    }

    /**
     * Set if the server requires authorization.
     *
     * @param bool $authRequired if auth is required
     */
    public function setAuthRequired(bool $authRequired): void
    {
        $this->authRequired = $authRequired;
    }

    /**
     * Check if server requires TLS.
     *
     * @return bool if TLS is required
     */
    public function isTLSRequired(): bool
    {
        return $this->TLSRequired;
    }

    /**
     * Set if server requires TLS.
     *
     * @param bool $TLSRequired if TLS is required
     */
    public function setTLSRequired(bool $TLSRequired): void
    {
        $this->TLSRequired = $TLSRequired;
    }

    /**
     * Check if TLS certificate is verified.
     *
     * @return bool if TLS certificate is verified
     */
    public function isTLSVerify(): bool
    {
        return $this->TLSVerify;
    }

    /**
     * Set if server verifies TLS certificate.
     *
     * @param bool $TLSVerify if TLS certificate is verified
     */
    public function setTLSVerify(bool $TLSVerify): void
    {
        $this->TLSVerify = $TLSVerify;
    }

    /**
     * Check if SSL is required.
     *
     * @return bool if SSL is required
     */
    public function isSSLRequired(): bool
    {
        return $this->SSLRequired;
    }

    /**
     * Set if SSL is required.
     *
     * @param bool $SSLRequired if SSL is required
     */
    public function setSSLRequired(bool $SSLRequired): void
    {
        $this->SSLRequired = $SSLRequired;
    }

    /**
     * Get the max size of the payload.
     *
     * @return int size in bytes
     */
    public function getMaxPayload(): int
    {
        return $this->maxPayload;
    }

    /**
     * Set the max size of the payload.
     *
     * @param int $maxPayload size in bytes
     */
    public function setMaxPayload(int $maxPayload): void
    {
        $this->maxPayload = $maxPayload;
    }

    /**
     * Get the server connection URLs.
     *
     * @return array list of server connection urls
     */
    public function getConnectURLs(): array
    {
        return $this->connectURLs;
    }

    /**
     * Set the server connection URLs.
     *
     * @param array $connectURLs list of server connection urls
     */
    public function setConnectURLs(array $connectURLs): void
    {
        $this->connectURLs = $connectURLs;
    }
}
