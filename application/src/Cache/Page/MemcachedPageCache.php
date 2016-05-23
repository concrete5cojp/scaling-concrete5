<?php
namespace Application\Src\Cache\Page;

use Concrete\Core\Cache\Page\PageCache;
use Concrete\Core\Cache\Page\PageCacheRecord;
use Stash\Driver\Memcache;
use Stash\Pool;
use Page as ConcretePage;
use Config;

class MemcachedPageCache extends PageCache
{
    public static $pool;

    public function __construct()
    {
        $driver = new Memcache();
        $driver->setOptions(Config::get('concrete.cache.memcached'));

        self::$pool = new Pool($driver);
    }
    
    public function getRecord($mixed)
    {
        $item = $this->getCacheItem($mixed);
        $record = $item->get();
        if ($record instanceof PageCacheRecord) {
            return $record;
        }
    }

    public function set(ConcretePage $c, $content)
    {
        if ($content) {
            $item = $this->getCacheItem($c);

            // Let other processes know that this one is rebuilding the data.
            $item->lock();

            $lifetime = $c->getCollectionFullPageCachingLifetimeValue();
            $response = new PageCacheRecord($c, $content, $lifetime);
            $item->set($response);
        }
    }

    public function purgeByRecord(PageCacheRecord $rec)
    {
        $item = $this->getCacheItem($rec);
        if ($item !== null) {
            $item->clear();
        }
    }

    public function purge(ConcretePage $c)
    {
        $item = $this->getCacheItem($c);
        if ($item !== null) {
            $item->clear();
        }
    }

    public function flush()
    {
        self::$pool->flush();
    }

    protected function getCacheItem($mixed)
    {
        $key = $this->getCacheKey($mixed);
        if ($key) {
            return self::$pool->getItem($key);
        }
    }
}
