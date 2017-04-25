# Tutorial for scalling concrete5

Make your concrete5 site scalable with Memcache!

Works on concrete5 version 8+

## How to setup

1. Upload `application/src` directory to your concrete5 site

2. Register a class override in your `application/bootstrap/app.php`

    ```php
    $app->bind('Concrete\Core\Session\SessionFactoryInterface', 'Application\Concrete\Session\SessionFactory');
    ```

3. Modify your `application/config/concrete.php`

    ```php
    <?php
    
    return [
        'session' => [
            'handler' => 'memcached',
            'memcached' => [
                'servers' => [
                    [
                        'host' => '127.0.0.1',
                        'port' => 11211,
                    ],
                ],
            ],
        ],
        'cache' => [
            'page' => [
                'adapter' => 'memcached',
                'memcached' => [
                    'servers' => [
                        [
                            'host' => '127.0.0.1',
                            'port' => 11211,
                        ],
                    ],
                ],
            ],
        ],
    ];
    ```
