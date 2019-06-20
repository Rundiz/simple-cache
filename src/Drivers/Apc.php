<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Drivers;


use Psr\SimpleCache\CacheInterface;


use Rundiz\SimpleCache\Exceptions\InvalidArgumentException;


/**
 * APC driver class.
 * 
 * Due to it is no longer supported and there is no extension for PHP 7.0 then there is no tests.<br>
 * Use at your own risk.
 * 
 * @deprecated since 3.0 Due to APC extension is no longer supported. Will be remove in next major version.
 * @link https://en.wikipedia.org/wiki/List_of_PHP_accelerators#Compatibility_chart APC no longer support
 * @link https://cometcache.com/blog/php-apc-extension-no-longer-supported/ APC no longer support
 * @since 3.0
 */
class Apc implements CacheInterface
{


    use MultipleTrait;


    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        return apc_clear_cache() && apc_clear_cache('user');
    }// clear


    /**
     * {@inheritDoc}
     */
    public function delete($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        if (!apc_exists($key)) {
            return true;
        }

        return apc_delete($key);
    }// delete


    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        $value = apc_fetch($key, $success);
        if (!$success) {
            return $default;
        }
        return $value;
    }// get


    /**
     * {@inheritDoc}
     */
    public function has($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        return apc_exists($key);
    }// has


    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        if ($ttl === null || (!is_int($ttl) && !$ttl instanceof \DateInterval)) {
            $ttl = 60;
        }

        if ($ttl instanceof \DateInterval) {
            $ttl = (new DateTime('now'))->add($ttl)->getTimeStamp() - time();
        } elseif (!is_int($ttl)) {
            throw new InvalidArgumentException('The $ttl must be integer or \\DateInterval object.');
        }

        return apc_store($key, $value, (int) $ttl);
    }// set


// delete


}
