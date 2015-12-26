# Tutorial for scalling concrete5

Make your concrete5 site scalable with Memcache!

## How to setup

1. Upload `application/src` directory to your concrete5 site

2. Modify your `application/config/concrete.php`

```php
return array(
    // Change full page caching adapter to memcache
    'cache' => array(
        'page' => array(
            'adapter' => 'memcache',
            'memcache' => array(
                'servers' => array(
                    array(
                        'host' => 'example.domain.of.memcache.cache.amazonaws.com',
                        'port' => '11211',
                    ),
                ),
            ),
        ),
    ),
    // Change session handler to memcache
    'session' => array(
        'handler' => 'memcache',
        'memcache' => array(
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

3. Register a class override in your `application/bootstrap/app.php`

```php
Core::singleton('session', function() {
    return Application\Src\Session\Session::start();
});
```
