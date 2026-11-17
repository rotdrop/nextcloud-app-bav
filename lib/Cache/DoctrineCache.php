<?php

namespace OCA\BAV\Cache;

use OCA\BAV\Scoped\Symfony\Component\Cache\Adapter\AbstractAdapter;
use OCP\ICache;
use OCP\ICacheFactory;
use Psr\Log\LoggerInterface;

/**
 * PSR-6 bridge to Nextcloud's configured caches.
 */
class DoctrineCache extends AbstractAdapter
{
  private ICache $cache;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(ICacheFactory $cacheFactory, LoggerInterface $logger)
  {
    parent::__construct();
    $this->setLogger($logger);

    if ($cacheFactory->isLocalCacheAvailable()) {
      $this->cache = $cacheFactory->createLocal('bav/doctrine/');
    } elseif ($cacheFactory->isAvailable()) {
      $this->cache = $cacheFactory->createDistributed('bav/doctrine/');
    } else {
      $this->cache = $cacheFactory->createInMemory();
    }
  }

  /** {@inheritdoc} */
  protected function doFetch(array $ids): iterable
  {
    foreach ($ids as $id) {
      $value = $this->cache->get($id);
      if ($value !== null && ($value[1] === 0 || $value[1] > microtime(true))) {
        yield $id => $value[0];
      }
    }
  }

  /** {@inheritdoc} */
  protected function doHave(string $id): bool
  {
    foreach ($this->doFetch([$id]) as $value) {
      return true;
    }
    return false;
  }

  /** {@inheritdoc} */
  protected function doClear(string $namespace): bool
  {
    return $this->cache->clear($namespace);
  }

  /** {@inheritdoc} */
  protected function doDelete(array $ids): bool
  {
    $success = true;
    foreach ($ids as $id) {
      $deleted = $this->cache->remove($id) || $this->cache->get($id) === null;
      $success = $deleted && $success;
    }
    return $success;
  }

  /** {@inheritdoc} */
  protected function doSave(array $values, int $lifetime): array|bool
  {
    $failed = [];
    $expires = $lifetime > 0 ? microtime(true) + $lifetime : 0;
    foreach ($values as $id => $value) {
      // Preserve null values and expiry even for Nextcloud's in-process cache.
      if (!$this->cache->set($id, [$value, $expires], $lifetime)) {
        $failed[] = $id;
      }
    }
    return $failed;
  }
}
