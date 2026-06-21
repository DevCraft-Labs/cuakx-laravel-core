<?php

namespace Cuakx\Core\Utils\Redis;

use Illuminate\Support\Facades\Redis;

/**
 * ORM-like Redis repository providing CRUD operations with optional key
 * prefixing, automatic JSON serialization/deserialization, and TTL management.
 *
 * Extend this class and pass a prefix to namespace keys per entity type,
 * or instantiate it directly for ad-hoc access.
 *
 * @example
 * // Direct usage
 * $repo = new RedisRepository('users:', 3600);
 * $repo->set('1', ['name' => 'Alice', 'email' => 'alice@example.com']);
 * $user = $repo->get('1');    // ['name' => 'Alice', 'email' => 'alice@example.com']
 * $repo->delete('1');
 *
 * // Subclass usage
 * class UserRedisRepository extends RedisRepository {
 *     public function __construct() {
 *         parent::__construct('users:', 3600);
 *     }
 * }
 */
class RedisRepository
{
    protected string $prefix;
    protected int $defaultTtl;
    protected string $connection;

    /**
     * @param string $prefix     Key prefix applied to all operations (e.g. "users:").
     *                           Helps namespace keys per entity/service.
     * @param int    $defaultTtl Default TTL in seconds applied when no TTL is specified.
     *                           Pass 0 (default) for no expiry.
     * @param string $connection Laravel Redis connection name as defined in config/database.php.
     *                           Defaults to 'default'.
     */
    public function __construct(
        string $prefix = '',
        int $defaultTtl = 0,
        string $connection = 'default'
    ) {
        $this->prefix     = $prefix;
        $this->defaultTtl = $defaultTtl;
        $this->connection = $connection;
    }

    /**
     * Stores a value under the given key.
     *
     * Arrays and objects are automatically JSON-encoded. Scalars are stored as strings.
     *
     * @param string   $key   The key (prefix is prepended automatically).
     * @param mixed    $value The value to store.
     * @param int|null $ttl   TTL in seconds. Null uses the instance default. 0 = no expiry.
     *
     * @return bool True on success.
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl        = $ttl ?? $this->defaultTtl;
        $fullKey    = $this->makeKey($key);
        $serialized = $this->serialize($value);

        if ($ttl > 0) {
            return (bool) Redis::connection($this->connection)->setex($fullKey, $ttl, $serialized);
        }

        return (bool) Redis::connection($this->connection)->set($fullKey, $serialized);
    }

    /**
     * Retrieves the value stored under the given key.
     *
     * JSON-encoded values are automatically decoded. Returns null if the key does not exist.
     *
     * @param string $key The key (prefix is prepended automatically).
     *
     * @return mixed The stored value, or null if the key does not exist.
     */
    public function get(string $key): mixed
    {
        $value = Redis::connection($this->connection)->get($this->makeKey($key));

        if ($value === null) {
            return null;
        }

        return $this->deserialize($value);
    }

    /**
     * Checks whether the given key exists.
     *
     * @param string $key The key (prefix is prepended automatically).
     *
     * @return bool True if the key exists.
     */
    public function exists(string $key): bool
    {
        return (bool) Redis::connection($this->connection)->exists($this->makeKey($key));
    }

    /**
     * Deletes the given key.
     *
     * @param string $key The key (prefix is prepended automatically).
     *
     * @return bool True if the key was deleted (false if it did not exist).
     */
    public function delete(string $key): bool
    {
        return (bool) Redis::connection($this->connection)->del($this->makeKey($key));
    }

    /**
     * Returns all keys matching the given glob-style pattern, scoped to this prefix.
     *
     * Keys are returned without the prefix so they can be fed back into other
     * methods on this repository.
     *
     * @param string $pattern Glob-style pattern (default: '*' returns all prefixed keys).
     *
     * @return array<string> List of matching keys with the prefix stripped.
     */
    public function keys(string $pattern = '*'): array
    {
        $rawKeys   = Redis::connection($this->connection)->keys($this->makeKey($pattern));
        $prefixLen = mb_strlen($this->prefix);

        return array_map(fn(string $k) => mb_substr($k, $prefixLen), $rawKeys);
    }

    /**
     * Retrieves multiple values by their keys in a single round-trip.
     *
     * @param array<string> $keys Array of keys (prefix is prepended to each).
     *
     * @return array<string, mixed> Associative array of key => deserialized value.
     *                              Missing keys are mapped to null.
     */
    public function getMany(array $keys): array
    {
        $fullKeys = array_map(fn(string $k) => $this->makeKey($k), $keys);
        $values   = Redis::connection($this->connection)->mget($fullKeys);

        $result = [];
        foreach ($keys as $i => $key) {
            $raw          = $values[$i] ?? null;
            $result[$key] = $raw !== null ? $this->deserialize($raw) : null;
        }

        return $result;
    }

    /**
     * Atomically increments the integer value stored under the given key.
     *
     * If the key does not exist it is initialised to 0 before incrementing.
     *
     * @param string $key The key (prefix is prepended automatically).
     * @param int    $by  Amount to increment by (default: 1).
     *
     * @return int The new value after incrementing.
     */
    public function increment(string $key, int $by = 1): int
    {
        return (int) Redis::connection($this->connection)->incrby($this->makeKey($key), $by);
    }

    /**
     * Atomically decrements the integer value stored under the given key.
     *
     * If the key does not exist it is initialised to 0 before decrementing.
     *
     * @param string $key The key (prefix is prepended automatically).
     * @param int    $by  Amount to decrement by (default: 1).
     *
     * @return int The new value after decrementing.
     */
    public function decrement(string $key, int $by = 1): int
    {
        return (int) Redis::connection($this->connection)->decrby($this->makeKey($key), $by);
    }

    /**
     * Sets or updates the expiry of an existing key.
     *
     * @param string $key     The key (prefix is prepended automatically).
     * @param int    $seconds TTL in seconds.
     *
     * @return bool True if the expiry was set, false if the key does not exist.
     */
    public function expire(string $key, int $seconds): bool
    {
        return (bool) Redis::connection($this->connection)->expire($this->makeKey($key), $seconds);
    }

    /**
     * Returns the remaining TTL of a key in seconds.
     *
     * Returns -1 if the key exists but has no associated expiry.
     * Returns -2 if the key does not exist.
     *
     * @param string $key The key (prefix is prepended automatically).
     *
     * @return int TTL in seconds, or a negative sentinel value.
     */
    public function ttl(string $key): int
    {
        return (int) Redis::connection($this->connection)->ttl($this->makeKey($key));
    }

    /**
     * Deletes all keys matching the given pattern scoped to this prefix.
     *
     * Useful for invalidating an entire cache namespace at once.
     *
     * @param string $pattern Glob-style pattern (default: '*' flushes all prefixed keys).
     *
     * @return int The number of keys deleted.
     */
    public function flush(string $pattern = '*'): int
    {
        $keys = Redis::connection($this->connection)->keys($this->makeKey($pattern));

        if (empty($keys)) {
            return 0;
        }

        return (int) Redis::connection($this->connection)->del($keys);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Prepends the instance prefix to a raw key.
     *
     * @param string $key The raw key segment.
     *
     * @return string The fully-qualified Redis key.
     */
    protected function makeKey(string $key): string
    {
        return $this->prefix . ":". $key;
    }

    /**
     * Serializes a value for storage in Redis.
     *
     * Arrays and objects are JSON-encoded; scalar values are cast to string.
     *
     * @param mixed $value The value to serialize.
     *
     * @return string The string representation ready for Redis storage.
     */
    protected function serialize(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Deserializes a raw Redis value.
     *
     * If the stored string is valid JSON it is decoded and returned as an
     * associative array; otherwise the raw string is returned as-is.
     *
     * @param string $value The raw Redis value.
     *
     * @return mixed The deserialized value.
     */
    protected function deserialize(string $value): mixed
    {
        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }
}
