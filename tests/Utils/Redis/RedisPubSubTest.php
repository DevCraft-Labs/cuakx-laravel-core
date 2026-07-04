<?php

namespace Cuakx\Core\Tests\Utils\Redis;

use Cuakx\Core\Tests\TestCase;
use Cuakx\Core\Utils\Redis\RedisPubSub;
use Illuminate\Support\Facades\Redis;
use Mockery;

class RedisPubSubTest extends TestCase
{
    public function test_publish_encodes_array_message_before_sending(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($connection);
        $connection->shouldReceive('publish')
            ->once()
            ->with('orders', '{"id":1,"status":"paid"}')
            ->andReturn(3);

        $pubSub = new RedisPubSub();

        $this->assertSame(3, $pubSub->publish('orders', ['id' => 1, 'status' => 'paid']));
    }

    public function test_subscribe_forwards_message_and_channel_to_callback(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($connection);
        $connection->shouldReceive('subscribe')
            ->once()
            ->withArgs(function (array $channels, callable $handler): bool {
                $this->assertSame(['orders'], $channels);
                $handler('payload-1', 'orders');

                return true;
            })
            ->andReturnNull();

        $receivedMessage = null;
        $receivedChannel = null;

        $pubSub = new RedisPubSub();
        $pubSub->subscribe('orders', function (string $message, string $channel) use (&$receivedMessage, &$receivedChannel): void {
            $receivedMessage = $message;
            $receivedChannel = $channel;
        });

        $this->assertSame('payload-1', $receivedMessage);
        $this->assertSame('orders', $receivedChannel);
    }

    public function test_psubscribe_forwards_message_channel_and_pattern_to_callback(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($connection);
        $connection->shouldReceive('psubscribe')
            ->once()
            ->withArgs(function (array $patterns, callable $handler): bool {
                $this->assertSame(['orders.*'], $patterns);
                $handler('payload-2', 'orders.created', 'orders.*');

                return true;
            })
            ->andReturnNull();

        $receivedMessage = null;
        $receivedChannel = null;
        $receivedPattern = null;

        $pubSub = new RedisPubSub();
        $pubSub->psubscribe('orders.*', function (string $message, string $channel, string $pattern) use (&$receivedMessage, &$receivedChannel, &$receivedPattern): void {
            $receivedMessage = $message;
            $receivedChannel = $channel;
            $receivedPattern = $pattern;
        });

        $this->assertSame('payload-2', $receivedMessage);
        $this->assertSame('orders.created', $receivedChannel);
        $this->assertSame('orders.*', $receivedPattern);
    }
}
