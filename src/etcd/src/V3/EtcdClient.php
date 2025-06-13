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

namespace Hyperf\Etcd\V3;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @license https://github.com/ouqiang/etcd-php/blob/master/LICENSE
 */
class EtcdClient
{
    // KV
    public const URI_PUT = 'kv/put';

    public const URI_RANGE = 'kv/range';

    public const URI_DELETE_RANGE = 'kv/deleterange';

    public const URI_TXN = 'kv/txn';

    public const URI_COMPACTION = 'kv/compaction';

    // Lease
    public const URI_GRANT = 'lease/grant';

    public const URI_REVOKE = 'kv/lease/revoke';

    public const URI_KEEPALIVE = 'lease/keepalive';

    public const URI_TIMETOLIVE = 'kv/lease/timetolive';

    // Role
    public const URI_AUTH_ROLE_ADD = 'auth/role/add';

    public const URI_AUTH_ROLE_GET = 'auth/role/get';

    public const URI_AUTH_ROLE_DELETE = 'auth/role/delete';

    public const URI_AUTH_ROLE_LIST = 'auth/role/list';

    // Authenticate
    public const URI_AUTH_ENABLE = 'auth/enable';

    public const URI_AUTH_DISABLE = 'auth/disable';

    public const URI_AUTH_AUTHENTICATE = 'auth/authenticate';

    // User
    public const URI_AUTH_USER_ADD = 'auth/user/add';

    public const URI_AUTH_USER_GET = 'auth/user/get';

    public const URI_AUTH_USER_DELETE = 'auth/user/delete';

    public const URI_AUTH_USER_CHANGE_PASSWORD = 'auth/user/changepw';

    public const URI_AUTH_USER_LIST = 'auth/user/list';

    public const URI_AUTH_ROLE_GRANT = 'auth/role/grant';

    public const URI_AUTH_ROLE_REVOKE = 'auth/role/revoke';

    public const URI_AUTH_USER_GRANT = 'auth/user/grant';

    public const URI_AUTH_USER_REVOKE = 'auth/user/revoke';

    public const PERMISSION_READ = 0;

    public const PERMISSION_WRITE = 1;

    public const PERMISSION_READWRITE = 2;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var bool 友好输出, 只返回所需字段
     */
    protected $pretty = false;

    /**
     * @var null|string auth token
     */
    protected $token;

    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;
    }

    public function setPretty($enabled)
    {
        $this->pretty = $enabled;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function clearToken()
    {
        $this->token = null;
    }

    // region kv

    /**
     * Put puts the given key into the key-value store.
     * A put request increments the revision of the key-value
     * store\nand generates one event in the event history.
     *
     * @param string $key
     * @param string $value
     * @param array $options 可选参数
     * @return array
     */
    public function put(
        $key,
        $value,
        #[ArrayShape([
            'lease' => 'int',
            'prev_kv' => 'bool',
            'ignore_value' => 'bool',
            'ignore_lease' => 'bool',
        ])]
        array $options = []
    ) {
        $params = [
            'key' => $key,
            'value' => $value,
        ];

        $params = $this->encode($params);
        $options = $this->encode($options);
        $body = $this->request(self::URI_PUT, $params, $options);
        $body = $this->decodeBodyForFields(
            $body,
            'prev_kv',
            ['key', 'value']
        );

        if (isset($body['prev_kv']) && $this->pretty) {
            return $this->convertFields($body['prev_kv']);
        }

        return $body;
    }

    /**
     * Gets the key or a range of keys.
     *
     * @param string $key
     * @return array
     */
    public function get(
        $key,
        #[ArrayShape([
            'range_end' => 'string',
            'limit' => 'int',
            'revision' => 'int',
            'sort_order' => 'int',
            'sort_target' => 'int',
            'serializable' => 'bool',
            'keys_only' => 'bool',
            'count_only' => 'bool',
            'min_mod_revision' => 'int',
            'max_mod_revision' => 'int',
            'min_create_revision' => 'int',
            'max_create_revision' => 'int',
        ])]
        array $options = []
    ) {
        $params = [
            'key' => $key,
        ];
        $params = $this->encode($params);
        $options = $this->encode($options);
        $body = $this->request(self::URI_RANGE, $params, $options);
        $body = $this->decodeBodyForFields(
            $body,
            'kvs',
            ['key', 'value']
        );

        if (isset($body['kvs']) && $this->pretty) {
            return $this->convertFields($body['kvs']);
        }

        return $body;
    }

    /**
     * get all keys.
     *
     * @return array
     */
    public function getAllKeys()
    {
        return $this->get("\0", ['range_end' => "\0"]);
    }

    /**
     * get all keys with prefix.
     *
     * @param string $prefix
     * @return array
     */
    public function getKeysWithPrefix($prefix)
    {
        $prefix = trim($prefix);
        if (! $prefix) {
            return [];
        }
        $lastIndex = strlen($prefix) - 1;
        $lastChar = $prefix[$lastIndex];
        $nextAsciiCode = ord($lastChar) + 1;
        $rangeEnd = $prefix;
        $rangeEnd[$lastIndex] = chr($nextAsciiCode);

        return $this->get($prefix, ['range_end' => $rangeEnd]);
    }

    /**
     * Removes the specified key or range of keys.
     *
     * @param string $key
     * @return array
     */
    public function del(
        $key,
        #[ArrayShape([
            'range_end' => 'string',
            'prev_kv' => 'bool',
        ])]
        array $options = []
    ) {
        $params = [
            'key' => $key,
        ];
        $params = $this->encode($params);
        $options = $this->encode($options);
        $body = $this->request(self::URI_DELETE_RANGE, $params, $options);
        $body = $this->decodeBodyForFields(
            $body,
            'prev_kvs',
            ['key', 'value']
        );

        if (isset($body['prev_kvs']) && $this->pretty) {
            return $this->convertFields($body['prev_kvs']);
        }

        return $body;
    }

    /**
     * Compact compacts the event history in the etcd key-value store.
     * The key-value\nstore should be periodically compacted
     * or the event history will continue to grow\nindefinitely.
     *
     * @param int $revision
     *
     * @param bool|false $physical
     *
     * @return array
     */
    public function compaction($revision, $physical = false)
    {
        $params = [
            'revision' => $revision,
            'physical' => $physical,
        ];

        return $this->request(self::URI_COMPACTION, $params);
    }

    // endregion kv

    // region lease

    /**
     * LeaseGrant creates a lease which expires if the server does not receive a
     * keepAlive\nwithin a given time to live period. All keys attached to the lease
     * will be expired and\ndeleted if the lease expires.
     * Each expired key generates a delete event in the event history.",.
     *
     * @param int $ttl TTL is the advisory time-to-live in seconds
     * @param int $id ID is the requested ID for the lease.
     *                If ID is set to 0, the lessor chooses an ID.
     * @return array
     */
    public function grant($ttl, $id = 0)
    {
        $params = [
            'TTL' => $ttl,
            'ID' => $id,
        ];

        return $this->request(self::URI_GRANT, $params);
    }

    /**
     * revokes a lease. All keys attached to the lease will expire and be deleted.
     *
     * @param int $id ID is the lease ID to revoke. When the ID is revoked,
     *                all associated keys will be deleted.
     * @return array
     */
    public function revoke($id)
    {
        $params = [
            'ID' => $id,
        ];

        return $this->request(self::URI_REVOKE, $params);
    }

    /**
     * keeps the lease alive by streaming keep alive requests
     * from the client\nto the server and streaming keep alive responses
     * from the server to the client.
     *
     * @param int $id ID is the lease ID for the lease to keep alive
     * @return array
     */
    public function keepAlive($id)
    {
        $params = [
            'ID' => $id,
        ];

        $body = $this->request(self::URI_KEEPALIVE, $params);

        if (! isset($body['result'])) {
            return $body;
        }
        // response "result" field, etcd bug?
        return [
            'ID' => $body['result']['ID'],
            'TTL' => $body['result']['TTL'],
        ];
    }

    /**
     * retrieves lease information.
     *
     * @param int $id ID is the lease ID for the lease
     * @param bool|false $keys
     * @return array
     */
    public function timeToLive($id, $keys = false)
    {
        $params = [
            'ID' => $id,
            'keys' => $keys,
        ];

        $body = $this->request(self::URI_TIMETOLIVE, $params);

        if (isset($body['keys'])) {
            $body['keys'] = array_map(function ($value) {
                return base64_decode($value);
            }, $body['keys']);
        }

        return $body;
    }

    // endregion lease

    // region auth

    /**
     * enable authentication.
     *
     * @return array
     */
    public function authEnable()
    {
        $body = $this->request(self::URI_AUTH_ENABLE);
        $this->clearToken();

        return $body;
    }

    /**
     * disable authentication.
     *
     * @return array
     */
    public function authDisable()
    {
        $body = $this->request(self::URI_AUTH_DISABLE);
        $this->clearToken();

        return $body;
    }

    /**
     * @param string $user
     * @param string $password
     * @return array
     */
    public function authenticate($user, $password)
    {
        $params = [
            'name' => $user,
            'password' => $password,
        ];

        $body = $this->request(self::URI_AUTH_AUTHENTICATE, $params);
        if ($this->pretty && isset($body['token'])) {
            return $body['token'];
        }

        return $body;
    }

    /**
     * add a new role.
     *
     * @param string $name
     * @return array
     */
    public function addRole($name)
    {
        $params = [
            'name' => $name,
        ];

        return $this->request(self::URI_AUTH_ROLE_ADD, $params);
    }

    /**
     * get detailed role information.
     *
     * @param string $role
     * @return array
     */
    public function getRole($role)
    {
        $params = [
            'role' => $role,
        ];

        $body = $this->request(self::URI_AUTH_ROLE_GET, $params);
        $body = $this->decodeBodyForFields(
            $body,
            'perm',
            ['key', 'range_end']
        );
        if ($this->pretty && isset($body['perm'])) {
            return $body['perm'];
        }

        return $body;
    }

    /**
     * delete a specified role.
     *
     * @param string $role
     * @return array
     */
    public function deleteRole($role)
    {
        $params = [
            'role' => $role,
        ];

        return $this->request(self::URI_AUTH_ROLE_DELETE, $params);
    }

    /**
     * get lists of all roles.
     *
     * @return array
     */
    public function roleList()
    {
        $body = $this->request(self::URI_AUTH_ROLE_LIST);

        if ($this->pretty && isset($body['roles'])) {
            return $body['roles'];
        }

        return $body;
    }

    /**
     * add a new user.
     *
     * @param string $user
     * @param string $password
     * @return array
     */
    public function addUser($user, $password)
    {
        $params = [
            'name' => $user,
            'password' => $password,
        ];

        return $this->request(self::URI_AUTH_USER_ADD, $params);
    }

    /**
     * get detailed user information.
     *
     * @param string $user
     * @return array
     */
    public function getUser($user)
    {
        $params = [
            'name' => $user,
        ];

        $body = $this->request(self::URI_AUTH_USER_GET, $params);
        if ($this->pretty && isset($body['roles'])) {
            return $body['roles'];
        }

        return $body;
    }

    /**
     * delete a specified user.
     *
     * @param string $user
     * @return array
     */
    public function deleteUser($user)
    {
        $params = [
            'name' => $user,
        ];

        return $this->request(self::URI_AUTH_USER_DELETE, $params);
    }

    /**
     * get a list of all users.
     *
     * @return array
     */
    public function userList()
    {
        $body = $this->request(self::URI_AUTH_USER_LIST);
        if ($this->pretty && isset($body['users'])) {
            return $body['users'];
        }

        return $body;
    }

    /**
     * change the password of a specified user.
     *
     * @param string $user
     * @param string $password
     * @return array
     */
    public function changeUserPassword($user, $password)
    {
        $params = [
            'name' => $user,
            'password' => $password,
        ];

        return $this->request(self::URI_AUTH_USER_CHANGE_PASSWORD, $params);
    }

    /**
     * grant a permission of a specified key or range to a specified role.
     *
     * @param string $role
     * @param int $permType
     * @param string $key
     * @param null|string $rangeEnd
     * @return array
     */
    public function grantRolePermission($role, $permType, $key, $rangeEnd = null)
    {
        $params = [
            'name' => $role,
            'perm' => [
                'permType' => $permType,
                'key' => base64_encode($key),
            ],
        ];
        if ($rangeEnd !== null) {
            $params['perm']['range_end'] = base64_encode($rangeEnd);
        }

        return $this->request(self::URI_AUTH_ROLE_GRANT, $params);
    }

    /**
     * revoke a key or range permission of a specified role.
     *
     * @param string $role
     * @param string $key
     * @param null|string $rangeEnd
     */
    public function revokeRolePermission($role, $key, $rangeEnd = null)
    {
        $params = [
            'role' => $role,
            'key' => $key,
        ];
        if ($rangeEnd !== null) {
            $params['range_end'] = $rangeEnd;
        }

        return $this->request(self::URI_AUTH_ROLE_REVOKE, $params);
    }

    /**
     * grant a role to a specified user.
     *
     * @param string $user
     * @param string $role
     * @return array
     */
    public function grantUserRole($user, $role)
    {
        $params = [
            'user' => $user,
            'role' => $role,
        ];

        return $this->request(self::URI_AUTH_USER_GRANT, $params);
    }

    /**
     * revoke a role of specified user.
     *
     * @param string $user
     * @param string $role
     * @return array
     */
    public function revokeUserRole($user, $role)
    {
        $params = [
            'name' => $user,
            'role' => $role,
        ];

        return $this->request(self::URI_AUTH_USER_REVOKE, $params);
    }

    // endregion auth

    /**
     * 发送HTTP请求
     *
     * @param string $uri
     * @param array $params 请求参数
     * @param array $options 可选参数
     * @return array
     * @throws BadResponseException
     */
    protected function request($uri, array $params = [], array $options = [])
    {
        if ($options) {
            $params = array_merge($params, $options);
        }
        // 没有参数, 设置一个默认参数
        if (! $params) {
            $params['php-etcd-client'] = 1;
        }
        $data = [
            'json' => $params,
        ];
        if ($this->token) {
            $data['headers'] = ['Grpc-Metadata-Token' => $this->token];
        }

        $response = $this->httpClient->request('post', $uri, $data);
        $content = (string) $response->getBody();

        $body = json_decode($content, true);
        if ($this->pretty && isset($body['header'])) {
            unset($body['header']);
        }

        return $body;
    }

    /**
     * string类型key用base64编码
     *
     * @return array
     */
    protected function encode(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = base64_encode($value);
            }
        }

        return $data;
    }

    /**
     * 指定字段base64解码
     *
     * @param string $bodyKey
     * @param array $fields 需要解码的字段
     * @return array
     */
    protected function decodeBodyForFields(array $body, $bodyKey, array $fields)
    {
        if (! isset($body[$bodyKey])) {
            return $body;
        }
        $data = $body[$bodyKey];
        if (! isset($data[0])) {
            $data = [$data];
        }
        foreach ($data as $key => $value) {
            foreach ($fields as $field) {
                if (isset($value[$field])) {
                    $data[$key][$field] = base64_decode($value[$field]);
                }
            }
        }

        if (isset($body[$bodyKey][0])) {
            $body[$bodyKey] = $data;
        } else {
            $body[$bodyKey] = $data[0];
        }

        return $body;
    }

    protected function convertFields(array $data)
    {
        if (! isset($data[0])) {
            return $data['value'];
        }

        $map = [];
        foreach ($data as $value) {
            $key = $value['key'];
            $map[$key] = $value['value'];
        }

        return $map;
    }
}
