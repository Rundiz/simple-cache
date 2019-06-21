<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Drivers;


use Psr\SimpleCache\CacheInterface;


use Rundiz\SimpleCache\Exceptions\InvalidArgumentException;


/**
 * Memcached driver.
 * 
 * @since 3.0
 */
class Memcached implements CacheInterface
{


    use MultipleTrait;


    /**
     * Memcached class.
     * @var \Memcached
     */
    protected $Memcached;


    /**
     * Class constructor.
     * 
     * @param \Memcached $Memcached Memcached class.
     */
    public function __construct(\Memcached $Memcached)
    {
        $this->Memcached = $Memcached;
    }// __construct

    
    public function clear(): bool
    {
        return $this->Memcached->flush();
    }// clear


    public function delete($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        return $this->Memcached->delete($key);
    }// delete


    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        $result = $this->Memcached->get($key);
        if ($result === false && $this->Memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return $default;
        }

        return $result;
    }// get


    /**
     * Get Memcached object for more usability.
     * 
     * @return \Memcached
     */
    public function getMemcached(): \Memcached
    {
        return $this->Memcached;
    }// getMemcached


    public function has($key): bool
    {
        return $this->get($key, $this) !== $this;
    }// has


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

        return $this->Memcached->set($key, $value, $expires);
    }// set


}
