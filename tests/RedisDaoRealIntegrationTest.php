<?php

declare(strict_types=1);

namespace SaToken\Tests;

use PHPUnit\Framework\TestCase;
use SaToken\Dao\SaTokenDaoRedis;

class RedisDaoRealIntegrationTest extends TestCase
{
    protected \Redis $redis;
    protected SaTokenDaoRedis $dao;
    protected string $prefix = '';

    protected function setUp(): void
    {
        if (!class_exists(\Redis::class)) {
            $this->markTestSkipped('Redis extension is not installed.');
        }

        $host = $_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?: '';
        $port = $_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?: '';

        if (!is_string($host) || $host === '' || !is_scalar($port) || (int) $port <= 0) {
            $this->markTestSkipped('REDIS_HOST / REDIS_PORT not configured.');
        }

        $redis = new \Redis();
        $connected = @$redis->connect($host, (int) $port, 1.5);
        if ($connected !== true) {
            $this->markTestSkipped('Unable to connect to Redis test service.');
        }

        $this->redis = $redis;
        $this->dao = new SaTokenDaoRedis([], $redis);
        $this->prefix = 'satoken:test:' . bin2hex(random_bytes(6)) . ':';
    }

    protected function tearDown(): void
    {
        if (isset($this->redis) && $this->prefix !== '') {
            $keys = $this->redis->keys($this->prefix . '*');
            if (is_array($keys) && $keys !== []) {
                $this->redis->del($keys);
            }
        }
    }

    public function testSetGetExpireAndDeleteAgainstRealRedis(): void
    {
        $this->assertInstanceOf(SaTokenDaoRedis::class, $this->dao);

        $key = $this->prefix . 'basic';
        $this->dao->set($key, 'value-1', 60);

        $this->assertSame('value-1', $this->dao->get($key));
        $this->assertTrue($this->dao->exists($key));

        $ttl = $this->dao->getTimeout($key);
        $this->assertGreaterThan(0, $ttl);
        $this->assertLessThanOrEqual(60, $ttl);

        $this->dao->delete($key);
        $this->assertNull($this->dao->get($key));
        $this->assertFalse($this->dao->exists($key));
    }

    public function testGetAndDeleteAgainstRealRedis(): void
    {
        $this->assertInstanceOf(SaTokenDaoRedis::class, $this->dao);

        $key = $this->prefix . 'get-and-delete';
        $this->dao->set($key, 'value-2', 60);

        $value = $this->dao->getAndDelete($key);
        $this->assertSame('value-2', $value);
        $this->assertNull($this->dao->get($key));
    }

    public function testGetAndExpireAgainstRealRedis(): void
    {
        $this->assertInstanceOf(SaTokenDaoRedis::class, $this->dao);

        $key = $this->prefix . 'get-and-expire';
        $this->dao->set($key, 'value-3', 10);

        $value = $this->dao->getAndExpire($key, 120);
        $this->assertSame('value-3', $value);

        $ttl = $this->dao->getTimeout($key);
        $this->assertGreaterThan(0, $ttl);
        $this->assertLessThanOrEqual(120, $ttl);
    }
}
