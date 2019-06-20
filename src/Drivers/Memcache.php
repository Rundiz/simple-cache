<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Drivers;


use Psr\SimpleCache\CacheInterface;


use Rundiz\SimpleCache\Exceptions\InvalidArgumentException;


/**
 * Memcache driver.
 * 
 * @since 3.0
 */
class Memcache implements CacheInterface
{


    use MultipleTrait;


    /**
     * Memcache class.
     * @var \Memcache 
     */
    protected $Memcache;


    /**
     * Class constructor.
     * 
     * @param \Memcache $memcache Memcache class.
     */
    public function __construct(\Memcache $memcache)
    {
        $this->Memcache = $memcache;
    }// __construct

    
    public function clear(): bool
    {
        return $this->Memcache->flush();
    }// clear


    public function delete($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        return $this->Memcache->delete($key);
    }// delete


    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        $flags = false;
        $value = $this->Memcache->get($key, $flags);
        if ($flags !== false) {
            return $value;
        }
        return $default;
    }// get


    /**
     * Get Memcache object for more usability.
     * 
     * @return \Memcache
     */
    public function getMemcache(): \Memcache
    {
        return $this->Memcache;
    }// getMemcache


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

        return $this->Memcache->set($key, $value, 0, $expires);
    }// set


}
