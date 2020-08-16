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
     *
     * @var string
     */
    private $serverID;

    /**
     * Server hostname.
     *
     * @var string
     */
    private $host;

    /**
     * Server port.
     *
     * @var int
     */
    private $port;

    /**
     * Server version number.
     *
     * @var string
     */
    private $version;

    /**
     * Server Golang version.
     *
     * @var string
     */
    private $goVersion;

    /**
     * Is authorization required?
     *
     * @var bool
     */
    private $authRequired;

    /**
     * Is TLS required?
     *
     * @var bool
     */
    private $TLSRequired;

    /**
     * Should TLS be verified?
     *
     * @var bool
     */
    private $TLSVerify;

    /**
     * Is SSL required?
     *
     * @var bool
     */
    private $SSLRequired;

    /**
     * Max payload size.
     *
     * @var int
     */
    private $maxPayload;

    /**
     * Connection URL list.
     *
     * @var array
     */
    private $connectURLs;

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
    public function getServerID()
    {
        return $this->serverID;
    }

    /**
     * Set the server ID.
     *
     * @param string $serverID server ID
     */
    public function setServerID($serverID)
    {
        $this->serverID = $serverID;
    }

    /**
     * Get the server host name or ip.
     *
     * @return string server host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the server host name or ip.
     *
     * @param string $host server host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Get server port number.
     *
     * @return int server port number
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set server port number.
     *
     * @param int $port server port number
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Get server version number.
     *
     * @return string server version number
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set server version number.
     *
     * @param string $version server version number
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get the golang version number.
     *
     * @return string go version number
     */
    public function getGoVersion()
    {
        return $this->goVersion;
    }

    /**
     * Set the golang version number.
     *
     * @param string $goVersion go version number
     */
    public function setGoVersion($goVersion)
    {
        $this->goVersion = $goVersion;
    }

    /**
     * Check if server requires authorization.
     *
     * @return bool if auth is required
     */
    public function isAuthRequired()
    {
        return $this->authRequired;
    }

    /**
     * Set if the server requires authorization.
     *
     * @param bool $authRequired if auth is required
     */
    public function setAuthRequired($authRequired)
    {
        $this->authRequired = $authRequired;
    }

    /**
     * Check if server requires TLS.
     *
     * @return bool if TLS is required
     */
    public function isTLSRequired()
    {
        return $this->TLSRequired;
    }

    /**
     * Set if server requires TLS.
     *
     * @param bool $TLSRequired if TLS is required
     */
    public function setTLSRequired($TLSRequired)
    {
        $this->TLSRequired = $TLSRequired;
    }

    /**
     * Check if TLS certificate is verified.
     *
     * @return bool if TLS certificate is verified
     */
    public function isTLSVerify()
    {
        return $this->TLSVerify;
    }

    /**
     * Set if server verifies TLS certificate.
     *
     * @param bool $TLSVerify if TLS certificate is verified
     */
    public function setTLSVerify($TLSVerify)
    {
        $this->TLSVerify = $TLSVerify;
    }

    /**
     * Check if SSL is required.
     *
     * @return bool if SSL is required
     */
    public function isSSLRequired()
    {
        return $this->SSLRequired;
    }

    /**
     * Set if SSL is required.
     *
     * @param bool $SSLRequired if SSL is required
     */
    public function setSSLRequired($SSLRequired)
    {
        $this->SSLRequired = $SSLRequired;
    }

    /**
     * Get the max size of the payload.
     *
     * @return int size in bytes
     */
    public function getMaxPayload()
    {
        return $this->maxPayload;
    }

    /**
     * Set the max size of the payload.
     *
     * @param int $maxPayload size in bytes
     */
    public function setMaxPayload($maxPayload)
    {
        $this->maxPayload = $maxPayload;
    }

    /**
     * Get the server connection URLs.
     *
     * @return array list of server connection urls
     */
    public function getConnectURLs()
    {
        return $this->connectURLs;
    }

    /**
     * Set the server connection URLs.
     *
     * @param array $connectURLs list of server connection urls
     */
    public function setConnectURLs(array $connectURLs)
    {
        $this->connectURLs = $connectURLs;
    }
}
