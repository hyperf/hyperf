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

use Traversable;

/**
 * ConnectionOptions Class.
 */
class ConnectionOptions
{
    /**
     * Hostname or IP to connect.
     *
     * @var string
     */
    private $host = 'localhost';

    /**
     * Port number to connect.
     *
     * @var int
     */
    private $port = 4222;

    /**
     * Username to connect.
     *
     * @var string
     */
    private $user;

    /**
     * Password to connect.
     *
     * @var string
     */
    private $pass;

    /**
     * Token to connect.
     *
     * @var string
     */
    private $token;

    /**
     * Language of this client.
     *
     * @var string
     */
    private $lang = 'php';

    /**
     * Version of this client.
     *
     * @var string
     */
    private $version = '0.8.2';

    /**
     * If verbose mode is enabled.
     *
     * @var bool
     */
    private $verbose = false;

    /**
     * If pedantic mode is enabled.
     *
     * @var bool
     */
    private $pedantic = false;

    /**
     * If reconnect mode is enabled.
     *
     * @var bool
     */
    private $reconnect = true;

    /**
     * Allows to define parameters which can be set by passing them to the class constructor.
     *
     * @var array
     */
    private $configurable = [
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
    public function __construct($options = null)
    {
        if (empty($options) === false) {
            $this->initialize($options);
        }
    }

    /**
     * Get the options JSON string.
     *
     * @return string
     */
    public function __toString()
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
     *
     * @return string
     */
    public function getAddress()
    {
        return 'tcp://' . $this->host . ':' . $this->port;
    }

    /**
     * Get host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set host.
     *
     * @param string $host host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get port.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set port.
     *
     * @param int $port port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get user.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param string $user user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Set password.
     *
     * @param string $pass password
     *
     * @return $this
     */
    public function setPass($pass)
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token.
     *
     * @param string $token token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set language.
     *
     * @param string $lang language
     *
     * @return $this
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version.
     *
     * @param string $version version number
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get verbose.
     *
     * @return bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * Set verbose.
     *
     * @param bool $verbose verbose flag
     *
     * @return $this
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;

        return $this;
    }

    /**
     * Get pedantic.
     *
     * @return bool
     */
    public function isPedantic()
    {
        return $this->pedantic;
    }

    /**
     * Set pedantic.
     *
     * @param bool $pedantic pedantic flag
     *
     * @return $this
     */
    public function setPedantic($pedantic)
    {
        $this->pedantic = $pedantic;

        return $this;
    }

    /**
     * Get reconnect.
     *
     * @return bool
     */
    public function isReconnect()
    {
        return $this->reconnect;
    }

    /**
     * Set reconnect.
     *
     * @param bool $reconnect reconnect flag
     *
     * @return $this
     */
    public function setReconnect($reconnect)
    {
        $this->reconnect = $reconnect;

        return $this;
    }

    /**
     * Set the connection options.
     *
     * @param array|Traversable $options the connection options
     */
    public function setConnectionOptions($options)
    {
        $this->initialize($options);
    }

    /**
     * Initialize the parameters.
     *
     * @param array|mixed|Traversable $options the connection options
     *
     * @throws Exception when $options are an invalid type
     */
    protected function initialize($options)
    {
        if (is_array($options) === false && ($options instanceof Traversable) === false) {
            throw new Exception('The $options argument must be either an array or Traversable');
        }

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
