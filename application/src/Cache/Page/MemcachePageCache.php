<?php
namespace Application\Src\Cache\Page;

use Concrete\Core\Cache\Page\PageCache;
use Concrete\Core\Cache\Page\PageCacheRecord;
use Stash\Driver\Memcache;
use Stash\Pool;
use Page as ConcretePage;
use Config;

class MemcachePageCache extends PageCache
{
    static $pool;

    public function __construct()
    {
        $driver = new Memcache();
        $driver->setOptions(Config::get('concrete.cache.page.memcache'));

        self::$pool = new Pool($driver);
    }

    public function getRecord($mixed)
    {
        $key = $this->getCacheKey($mixed);
        if ($key) {
            $item = self::$pool->getItem($key);
            return $item->get();
        }
    }

    public function set(ConcretePage $c, $content)
    {
        $key = $this->getCacheKey($c);
        if ($key && $content) {
            $item = self::$pool->getItem($key);

            // Let other processes know that this one is rebuilding the data.
            $item->lock();

            $lifetime = $c->getCollectionFullPageCachingLifetimeValue();
            $response = new PageCacheRecord($c, $content, $lifetime);
            $item->set($response);
        }
    }

    public function purgeByRecord(\Concrete\Core\Cache\Page\PageCacheRecord $rec)
    {
        $key = $this->getCacheKey($rec);
        if ($key) {
            $item = self::$pool->getItem($key);
            $item->clear();
        }
    }

    public function purge(ConcretePage $c)
    {
        $key = $this->getCacheKey($c);
        if ($key) {
            $item = self::$pool->getItem($key);
            $item->clear();
        }
    }

    public function flush()
    {
        self::$pool->flush();
    }
}