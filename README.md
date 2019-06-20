# Simple Cache

The simple and easy PHP cache drivers for cache any PHP data type. It is implemented PSR-16.

[![Latest Stable Version](https://poser.pugx.org/rundiz/simple-cache/v/stable)](https://packagist.org/packages/rundiz/simple-cache)
[![License](https://poser.pugx.org/rundiz/simple-cache/license)](https://packagist.org/packages/rundiz/simple-cache)
[![Total Downloads](https://poser.pugx.org/rundiz/simple-cache/downloads)](https://packagist.org/packages/rundiz/simple-cache)

## Installation
Make sure that you have [Composer](http://getcomposer.org/) installed and then run the following command.

```
composer require rundiz/simple-cache
```

## Example
We currently support APC, APCu, Memcache, Memcached, File system drivers. These are how to initialize for each driver class.

```php
// For Memcache driver
$memcache = new \Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");
$SimpleCache = new \Rundiz\SimpleCache\Drivers\Memcached($memcache);
unset($memcache);

// For Memcached driver
$memcached = new \Memcached;
$memcached->addServer('localhost', 11211) or die ("Could not connect");
$SimpleCache = new \Rundiz\SimpleCache\Drivers\Memcached($memcached);
unset($memcached);

// For APC (deprecated, recommended use APCu instead).
$SimpleCache = new \Rundiz\SimpleCache\Drivers\Apc();

// For APCu
$SimpleCache = new \Rundiz\SimpleCache\Drivers\Apcu();

// For File system (very basic cache driver)
$SimpleCache = new \Rundiz\SimpleCache\Drivers\FileSystem();
```

Common methods to get, set, delete, or anything else please read more at [PSR-16 document](https://www.php-fig.org/psr/psr-16/).<br>
Here are few examples.

```php
// To get or fetch cache data.
$SimpleCache->get('cache_key');

// To save cache data.
$SimpleCache->set('cache_key', 'cache data. (any type of data... string, integer, double, array, object, etc.)', 90);

// To delete cache.
$SimpleCache->delete('cache_key');

// To clear all cached.
$SimpleCache->clear();
```

### Fallback cache drivers
You can set many cache drivers as fallback in case that some driver is not installed on the server.

```php
if (class_exists('\\Memcached')) {
    $memcached = new \Memcached;
    $memcached->addServer('localhost', 11211) or die ("Could not connect");
    $SimpleCache = new \Rundiz\SimpleCache\Drivers\Memcached($memcached);
    unset($memcached);
} elseif (class_exists('\\Memcache')) {
    $memcache = new \Memcache;
    $memcache->connect('localhost', 11211) or die ("Could not connect");
    $SimpleCache = new \Rundiz\SimpleCache\Drivers\Memcached($memcache);
    unset($memcache);
} elseif (function_exists('apcu_fetch')) {
    $SimpleCache = new \Rundiz\SimpleCache\Drivers\Apcu();
} else {
    $SimpleCache = new \Rundiz\SimpleCache\Drivers\FileSystem();
}
```

### Namespace/Sub folders for file system cache
In file system cache, you can use namespace or sub folders by just add "." (dot) to the cache id.<br>
For example: `$SimpleCache->set('Model.Accounts.id1', $userdata, 120);`. <br>
This will save the cache file to **Model/Accounts/id1** folder.