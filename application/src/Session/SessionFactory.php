<?php
namespace Application\Src\Session;

use Concrete\Core\Application\Application;
use Concrete\Core\Http\Request;
use Illuminate\Config\Repository;
use Concrete\Core\Session\SessionFactoryInterface;
use Concrete\Core\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

/**
 * Class SessionFactory
 * Customized concrete5 session factory
 * @package Concrete\Core\Session
 */
class SessionFactory implements SessionFactoryInterface
{
    /** @var \Concrete\Core\Application\Application */
    private $app;

    /** @var \Concrete\Core\Http\Request */
    private $request;

    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    /**
     * Create a new symfony session object
     * This method MUST NOT start the session
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function createSession()
    {
        $config = $this->app['config'];
        $storage = $this->getSessionStorage($config);

        $session = new SymfonySession($storage);
        $session->setName($config->get('concrete.session.name'));

        /**
         * @todo Move this to somewhere else
         */
        $this->request->setSession($session);

        return $session;
    }

    /**
     * @param \Illuminate\Config\Repository $config
     * @return \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface
     */
    private function getSessionStorage(Repository $config)
    {
        $app = $this->app;

        if ($app->isRunThroughCommandLineInterface()) {
            $storage = new MockArraySessionStorage();
        } else {
            $handler = $this->getSessionHandler($config);
            $storage = new NativeSessionStorage(array(), $handler);

            // Initialize the storage with some options
            $options = $config->get('concrete.session.cookie');
            if ($options['cookie_path'] === false) {
                $options['cookie_path'] = $app['app_relative_path'] . '/';
            }
            $options['gc_maxlifetime'] = $config->get('concrete.session.max_lifetime');
            $storage->setOptions($options);
        }

        return $storage;
    }

    /**
     * @param \Illuminate\Config\Repository $config
     * @return \SessionHandlerInterface
     */
    private function getSessionHandler(Repository $config)
    {
        if ($config->get('concrete.session.handler') == 'memcached' && class_exists('Memcached')) {
            $memcached = new \Memcached;
            $servers = $config->get('concrete.session.memcached.servers');
            foreach ($servers as $server) {
                $memcached->addServer($server['host'], $server['port']);
            }
            $handler = new MemcachedSessionHandler($memcached);
        } else {
            $savePath = $config->get('concrete.session.save_path') ?: null;
            $handler = new NativeFileSessionHandler($savePath);
        }

        return $handler;
    }
}
