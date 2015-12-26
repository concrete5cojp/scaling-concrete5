<?php
namespace Application\Src\Session;

use Config;
use Core;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler;

class Session extends \Concrete\Core\Session\Session
{
    public static function start()
    {
        if (Config::get('concrete.session.handler') == 'memcache' && class_exists('Memcache')) {
            $app = Core::make('app');
            if ($app->isRunThroughCommandLineInterface()) {
                $storage = new MockArraySessionStorage();
            } else {
                $servers = Config::get('concrete.session.memcache.servers');
                $memcache = new \Memcache;
                if (is_array($servers)) {
                    foreach ($servers as $server) {
                        $memcache->addServer($server['host'], $server['port']);
                    }
                }
                $storage = new NativeSessionStorage(array(), new MemcacheSessionHandler($memcache));
                $options = Config::get('concrete.session.cookie');
                if ($options['cookie_path'] === false) {
                    $options['cookie_path'] = $app['app_relative_path'] . '/';
                }
                $options['gc_max_lifetime'] = Config::get('concrete.session.max_lifetime');
                $storage->setOptions($options);
            }
    
            $session = new SymfonySession($storage);
            $session->setName(Config::get('concrete.session.name'));
    
            static::testSessionFixation($session);
            return $session;
        } else {
            return parent::start();
        }
    }
}