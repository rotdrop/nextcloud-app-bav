<?php

namespace OCA\BAV\Tests\Unit\Cache;

use OCA\BAV\Cache\DoctrineCache;
use OCP\Cache\CappedMemoryCache;
use OCP\ICache;
use OCP\ICacheFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DoctrineCacheTest extends TestCase
{
  public static function cacheBackends(): array
  {
    return [
      'local cache' => [true, true, 'createLocal'],
      'distributed cache without local cache' => [false, true, 'createDistributed'],
      'no configured cache' => [false, false, 'createInMemory'],
    ];
  }

  #[DataProvider('cacheBackends')]
  public function testSelectsConfiguredCache(bool $local, bool $distributed, string $method): void
  {
    $backend = new CappedMemoryCache();
    $factory = $this->createMock(ICacheFactory::class);
    $factory->method('isLocalCacheAvailable')->willReturn($local);
    $factory->method('isAvailable')->willReturn($distributed);
    foreach (['createLocal', 'createDistributed', 'createInMemory'] as $candidate) {
      if ($candidate === $method) {
        $invocation = $factory->expects($this->once())->method($candidate);
        if ($candidate !== 'createInMemory') {
          $invocation->with('bav/doctrine/');
        }
        $invocation->willReturn($backend);
      } else {
        $factory->expects($this->never())->method($candidate);
      }
    }

    $cache = new DoctrineCache($factory, new NullLogger());
    $metadata = (object)['entity' => 'Bank'];
    $this->assertTrue($cache->save($cache->getItem('metadata')->set($metadata)));
    $this->assertSame($metadata, $backend->get('metadata')[0]);
    $this->assertSame($metadata, $cache->getItem('metadata')->get());
  }

  public static function cachedValues(): array
  {
    return [[null], [false], [0], [''], [['metadata']]];
  }

  #[DataProvider('cachedValues')]
  public function testDistinguishesCachedValuesFromMisses(mixed $value): void
  {
    $cache = $this->makeCache(new CappedMemoryCache());
    $this->assertFalse($cache->getItem('missing')->isHit());
    $this->assertFalse($cache->hasItem('missing'));

    $this->assertTrue($cache->save($cache->getItem('metadata')->set($value)));
    $this->assertTrue($cache->hasItem('metadata'));
    $this->assertTrue($cache->getItem('metadata')->isHit());
    $this->assertSame($value, $cache->getItem('metadata')->get());
    $this->assertTrue($cache->deleteItem('metadata'));
    $this->assertFalse($cache->hasItem('metadata'));
  }

  public function testForwardsLifetimeAndClearPrefix(): void
  {
    $backend = $this->createMock(ICache::class);
    $backend->expects($this->once())->method('set')->with(
      'metadata',
      $this->anything(),
      $this->callback(static fn(int $ttl): bool => $ttl > 0 && $ttl <= 60),
    )->willReturn(true);
    $backend->expects($this->once())->method('clear')->with('bank_')->willReturn(true);
    $cache = $this->makeCache($backend);

    $this->assertTrue($cache->save($cache->getItem('metadata')->set('value')->expiresAfter(60)));
    $this->assertTrue($cache->clear('bank_'));
  }

  public function testDeletingMissingCacheEntrySucceeds(): void
  {
    $backend = $this->createMock(ICache::class);
    $backend->expects($this->once())->method('remove')->with('missing')->willReturn(false);
    $backend->method('get')->with('missing')->willReturn(null);

    $this->assertTrue($this->makeCache($backend)->deleteItem('missing'));
  }

  public function testExpiredInMemoryEntriesAreMisses(): void
  {
    $backend = new CappedMemoryCache();
    $cache = $this->makeCache($backend);
    $this->assertTrue($cache->save($cache->getItem('metadata')->set('value')));
    $entry = $backend->get('metadata');
    $entry[1] = microtime(true) - 1;
    $backend->set('metadata', $entry);

    $this->assertFalse($cache->getItem('metadata')->isHit());
    $this->assertFalse($cache->hasItem('metadata'));
  }

  public function testFailedWritesAreReportedAndLogged(): void
  {
    $backend = $this->createMock(ICache::class);
    $backend->method('set')->willReturn(false);
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('warning')->with(
      $this->stringContains('Failed to save key'),
      $this->anything(),
    );
    $cache = $this->makeCache($backend, $logger);

    $this->assertFalse($cache->save($cache->getItem('metadata')->set('value')));
  }

  private function makeCache(ICache $backend, ?LoggerInterface $logger = null): DoctrineCache
  {
    $factory = $this->createMock(ICacheFactory::class);
    $factory->method('isLocalCacheAvailable')->willReturn(true);
    $factory->method('createLocal')->willReturn($backend);
    return new DoctrineCache($factory, $logger ?? new NullLogger());
  }
}
