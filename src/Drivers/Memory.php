<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Drivers;


use Psr\SimpleCache\CacheInterface;


use Rundiz\SimpleCache\Exceptions\InvalidArgumentException;


/**
 * Memory cache (or array cache) class.
 * 
 * @since 3.0
 */
class Memory implements CacheInterface
{


    use MultipleTrait;


    /**
     * @var array Contain associative array where its key is the cache key. The values contain 2 array, first is cache value, second is expiration timestamp.
     */
    protected $data = [];


    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        $this->data = [];

        return true;
    }// clear


    /**
     * {@inheritDoc}
     */
    public function delete($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        unset($this->data[$key]);
        return true;
    }// delete


    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        if ($this->has($key)) {
            // if found key in data.
            list($value, $expireTime) = $this->data[$key];

            if ($expireTime < time()) {
                // if expired.
                unset($expireTime, $value);
                $this->delete($key);
                return $default;
            }

            $value = unserialize($value);
            unset($expireTime);
            return $value;
        } else {
            // if not found.
            return $default;
        }
    }// get


    /**
     * {@inheritDoc}
     */
    public function has($key): bool
    {
        return (is_array($this->data) && array_key_exists($key, $this->data) && count($this->data[$key]) == 2);
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

        if (is_int($ttl)) {
            $expires = (time() + $ttl);
        } elseif ($ttl instanceof \DateInterval) {
            $expires = date_create_from_format("U", time())->add($ttl)->getTimestamp();
        } else {
            throw new InvalidArgumentException('The $ttl must be integer or \\DateInterval object.');
        }

        $value = serialize($value);
        $this->data[$key] = [$value, $expires];

        return true;
    }// set


}
