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
use Traversable;

/**
 * ConnectionOptions Class.
 */
class ConnectionOptions implements Stringable
{
    /**
     * Hostname or IP to connect.
     */
    private string $host = 'localhost';

    /**
     * Port number to connect.
     */
    private int $port = 4222;

    /**
     * Username to connect.
     */
    private string $user;

    /**
     * Password to connect.
     */
    private string $pass;

    /**
     * Token to connect.
     */
    private string $token;

    /**
     * Language of this client.
     */
    private string $lang = 'php';

    /**
     * Version of this client.
     */
    private string $version = '0.8.2';

    /**
     * If verbose mode is enabled.
     */
    private bool $verbose = false;

    /**
     * If pedantic mode is enabled.
     */
    private bool $pedantic = false;

    /**
     * If reconnect mode is enabled.
     */
    private bool $reconnect = true;

    /**
     * Allows defining parameters which can be set by passing them to the class constructor.
     */
    private array $configurable = [
        'host',
        'port',
        'user',
        'pass',
        'token',
        'lang',
        'version',
        'verbose',
        'pedantic',
        'reconnect',
    ];

    /**
     * ConnectionOptions constructor.
     *
     * <code>
     * use Nats\ConnectionOptions;
     *
     * $options = new ConnectionOptions([
     *     'host' => '127.0.0.1',
     *     'port' => 4222,
     *     'user' => 'nats',
     *     'pass' => 'nats',
     *     'lang' => 'php',
     *      // ...
     * ]);
     * </code>
     *
     * @param array|Traversable $options the connection options
     */
    public function __construct(null|array|Traversable $options = null)
    {
        if (! empty($options)) {
            $this->initialize($options);
        }
    }

    /**
     * Get the options JSON string.
     */
    public function __toString(): string
    {
        $a = [
            'lang' => $this->lang,
            'version' => $this->version,
            'verbose' => $this->verbose,
            'pedantic' => $this->pedantic,
        ];
        if (empty($this->user) === false) {
            $a['user'] = $this->user;
        }

        if (empty($this->pass) === false) {
            $a['pass'] = $this->pass;
        }

        if (empty($this->token) === false) {
            $a['auth_token'] = $this->token;
        }

        return json_encode($a);
    }

    /**
     * Get the URI for a server.
     */
    public function getAddress(): string
    {
        return 'tcp://' . $this->host . ':' . $this->port;
    }

    /**
     * Get host.
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set host.
     */
    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get port.
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set port.
     */
    public function setPort(int $port): static
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get user.
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Set user.
     */
    public function setUser(string $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get password.
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * Set password.
     */
    public function setPass(string $pass): static
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Get token.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Set token.
     */
    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get language.
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * Set language.
     */
    public function setLang(string $lang): static
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set version.
     */
    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get verbose.
     */
    public function isVerbose(): bool
    {
        return $this->verbose;
    }

    /**
     * Set verbose.
     */
    public function setVerbose(bool $verbose): static
    {
        $this->verbose = $verbose;

        return $this;
    }

    /**
     * Get pedantic.
     */
    public function isPedantic(): bool
    {
        return $this->pedantic;
    }

    /**
     * Set pedantic.
     */
    public function setPedantic(bool $pedantic): static
    {
        $this->pedantic = $pedantic;

        return $this;
    }

    /**
     * Get reconnect.
     */
    public function isReconnect(): bool
    {
        return $this->reconnect;
    }

    /**
     * Set reconnect.
     */
    public function setReconnect(bool $reconnect): static
    {
        $this->reconnect = $reconnect;

        return $this;
    }

    /**
     * Set the connection options.
     */
    public function setConnectionOptions(array|Traversable $options): void
    {
        $this->initialize($options);
    }

    /**
     * Initialize the parameters.
     *
     * @param array|Traversable $options the connection options
     *
     * @throws Exception when $options are an invalid type
     */
    protected function initialize(array|Traversable $options)
    {
        foreach ($options as $key => $value) {
            if (in_array($key, $this->configurable, true) === false) {
                continue;
            }

            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method) === true) {
                $this->{$method}($value);
            }
        }
    }
}
