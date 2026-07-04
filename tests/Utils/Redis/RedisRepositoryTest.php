<?php

namespace Cuakx\Core\Tests\Utils\Redis;

use Cuakx\Core\Tests\TestCase;
use Cuakx\Core\Utils\Redis\RedisRepository;
use Illuminate\Support\Facades\Redis;
use Mockery;

class RedisRepositoryTest extends TestCase
{
    public function test_set_uses_setex_when_ttl_is_positive(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($connection);
        $connection->shouldReceive('setex')
            ->once()
            ->with('users:1', 60, '{"name":"Alice"}')
            ->andReturn(true);

        $repo = new RedisRepository('users', 60);
        $this->assertTrue($repo->set('1', ['name' => 'Alice']));
    }

    public function test_set_uses_set_when_ttl_is_zero(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($connection);
        $connection->shouldReceive('set')
            ->once()
            ->with('users:2', 'hello')
            ->andReturn(true);

        $repo = new RedisRepository('users', 0);
        $this->assertTrue($repo->set('2', 'hello'));
    }

    public function test_get_returns_deserialized_json_array(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($connection);
        $connection->shouldReceive('get')
            ->once()
            ->with('users:1')
            ->andReturn('{"name":"Alice"}');

        $repo = new RedisRepository('users');
        $this->assertSame(['name' => 'Alice'], $repo->get('1'));
    }

    public function test_get_returns_null_when_key_missing(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($connection);
        $connection->shouldReceive('get')->once()->with('users:missing')->andReturn(null);

        $repo = new RedisRepository('users');
        $this->assertNull($repo->get('missing'));
    }

    public function test_getMany_maps_existing_and_missing_keys(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->once()->with('default')->andReturn($connection);
        $connection->shouldReceive('mget')
            ->once()
            ->with(['users:1', 'users:2'])
            ->andReturn(['{"id":1}', null]);

        $repo = new RedisRepository('users');
        $this->assertSame(
            ['1' => ['id' => 1], '2' => null],
            $repo->getMany(['1', '2'])
        );
    }

    public function test_increment_decrement_expire_and_ttl_delegate_to_redis(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->times(4)->with('default')->andReturn($connection);
        $connection->shouldReceive('incrby')->once()->with('users:count', 2)->andReturn(10);
        $connection->shouldReceive('decrby')->once()->with('users:count', 1)->andReturn(9);
        $connection->shouldReceive('expire')->once()->with('users:count', 120)->andReturn(1);
        $connection->shouldReceive('ttl')->once()->with('users:count')->andReturn(88);

        $repo = new RedisRepository('users');

        $this->assertSame(10, $repo->increment('count', 2));
        $this->assertSame(9, $repo->decrement('count'));
        $this->assertTrue($repo->expire('count', 120));
        $this->assertSame(88, $repo->ttl('count'));
    }

    public function test_flush_deletes_matching_keys_and_returns_deleted_count(): void
    {
        $connection = Mockery::mock();

        Redis::shouldReceive('connection')->times(2)->with('default')->andReturn($connection);
        $connection->shouldReceive('keys')->once()->with('users:*')->andReturn(['users:1', 'users:2']);
        $connection->shouldReceive('del')->once()->with(['users:1', 'users:2'])->andReturn(2);

        $repo = new RedisRepository('users');
        $this->assertSame(2, $repo->flush());
    }
}
