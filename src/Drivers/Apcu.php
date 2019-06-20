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
 * @since 3.0
 */
class Apcu implements CacheInterface
{


    use MultipleTrait;


    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        return apcu_clear_cache();
    }// clear


    /**
     * {@inheritDoc}
     */
    public function delete($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        if (!apcu_exists($key)) {
            return true;
        }

        return apcu_delete($key);
    }// delete


    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        $value = apcu_fetch($key, $success);
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

        return apcu_exists($key);
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

        return apcu_store($key, $value, (int) $ttl);
    }// set


// delete


}
