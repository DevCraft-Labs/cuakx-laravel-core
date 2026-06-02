<?php

namespace Cuakx\Core\Utils\Redis;

use Illuminate\Support\Facades\Redis;

/**
 * Wrapper for Redis Pub/Sub operations using Laravel's Redis facade.
 *
 * Provides a clean API for publishing messages to channels and subscribing
 * to receive them. Both {@see subscribe()} and {@see psubscribe()} are
 * **blocking** — they run an infinite loop and should be invoked inside a
 * long-lived process such as an Artisan command or a dedicated queue worker.
 *
 * @example — Publishing
 * ```php
 * $pubsub = new RedisPubSub();
 * $pubsub->publish('orders', ['id' => 1, 'status' => 'paid']);
 * ```
 *
 * @example — Subscribing (inside an Artisan command handle())
 * ```php
 * $pubsub = new RedisPubSub();
 * $pubsub->subscribe('orders', function (string $message, string $channel) {
 *     $data = json_decode($message, true);
 *     // process $data ...
 * });
 * ```
 *
 * @example — Pattern subscribe
 * ```php
 * $pubsub->psubscribe('orders.*', function (string $message, string $channel, string $pattern) {
 *     // $channel = actual channel, $pattern = the matched pattern
 * });
 * ```
 */
class RedisPubSub
{
    protected string $connection;

    /**
     * @param string $connection Laravel Redis connection name as defined in config/database.php.
     *                           Defaults to 'default'.
     */
    public function __construct(string $connection = 'default')
    {
        $this->connection = $connection;
    }

    /**
     * Publishes a message to a Redis channel.
     *
     * Arrays and objects are automatically JSON-encoded before publishing.
     *
     * @param string $channel The channel name to publish to.
     * @param mixed  $message The message payload. Arrays/objects are JSON-encoded.
     *
     * @return int The number of subscribers that received the message.
     */
    public function publish(string $channel, mixed $message): int
    {
        $payload = (is_array($message) || is_object($message))
            ? json_encode($message)
            : (string) $message;

        return (int) Redis::connection($this->connection)->publish($channel, $payload);
    }

    /**
     * Subscribes to one or more Redis channels (blocking).
     *
     * The callback is invoked for every message received. This method blocks
     * the process indefinitely — run it inside an Artisan command or a
     * dedicated worker, never inside a normal HTTP request.
     *
     * Callback signature: `function (string $message, string $channel): void`
     *
     * @param string|array<string> $channels A channel name or array of channel names.
     * @param callable             $callback Invoked for each message.
     *                                       Receives (string $message, string $channel).
     *
     * @return void
     */
    public function subscribe(string|array $channels, callable $callback): void
    {
        $channels = (array) $channels;

        Redis::connection($this->connection)->subscribe(
            $channels,
            function (string $message, string $channel) use ($callback) {
                $callback($message, $channel);
            }
        );
    }

    /**
     * Subscribes to Redis channels matching one or more glob-style patterns (blocking).
     *
     * Useful when you need to listen to a family of channels without knowing
     * their exact names (e.g. "orders.*" catches "orders.created", "orders.updated").
     *
     * The callback is invoked for every message received. This method blocks
     * the process indefinitely — run it inside an Artisan command or a
     * dedicated worker, never inside a normal HTTP request.
     *
     * Callback signature: `function (string $message, string $channel, string $pattern): void`
     *
     * @param string|array<string> $patterns A pattern or array of patterns (e.g. "orders.*").
     * @param callable             $callback Invoked for each message.
     *                                       Receives (string $message, string $channel, string $pattern).
     *
     * @return void
     */
    public function psubscribe(string|array $patterns, callable $callback): void
    {
        $patterns = (array) $patterns;

        Redis::connection($this->connection)->psubscribe(
            $patterns,
            function (string $message, string $channel, string $pattern) use ($callback) {
                $callback($message, $channel, $pattern);
            }
        );
    }
}
