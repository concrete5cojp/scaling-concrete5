# Tutorial for scalling concrete5

Make your concrete5 site scalable with Memcache!

Works on concrete5 version 7.5.4+

## How to setup

1. Upload `application/src` directory to your concrete5 site

2. Register a class override in your `application/bootstrap/app.php`

```php
Core::bind('Concrete\Core\Session\SessionFactoryInterface', 'Application\Src\Session\SessionFactory');
```

3. Modify your `application/config/concrete.php`

```php
return array(
    // Change session handler to memcache
    'session' => array(
        'handler' => 'memcached',
        'memcached' => array(
            'servers' => array(
                array(
                    'host' => 'example.domain.of.memcache.cache.amazonaws.com',
                    'port' => '11211',
                ),
            ),
        ),
    ),
    // Change full page caching adapter to memcache
    'cache' => array(
        'overrides' => false,
        'page' => array(
            'adapter' => 'memcached',
        ),
        'memcached' => array(
            'servers' => array(
                array(
                    'host' => 'example.domain.of.memcache.cache.amazonaws.com',
                    'port' => '11211',
                ),
            ),
        ),
    ),
);
```
