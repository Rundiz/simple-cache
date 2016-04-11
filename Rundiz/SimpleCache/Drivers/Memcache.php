<?php
/**
 * PHP Simple Cache Memcache driver.
 * 
 * @package Simple Cache
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT
 * 
 */


namespace Rundiz\SimpleCache\Drivers;

use Rundiz\SimpleCache\SimpleCacheInterface;

/**
 * Memcache driver class
 *
 * @since 2.0
 */
class Memcache implements SimpleCacheInterface
{


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


    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->Memcache->flush();
    }// clear


    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $flags = null;
        $this->Memcache->get($id, $flags);

        if ($flags === null) {
            return false;
        }
        return $this->Memcache->delete($id);
    }// delete


    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        return $this->Memcache->get($id);
    }// get


    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifetime = 60)
    {
        if (!is_int($lifetime) || is_int($lifetime) && $lifetime <= 0) {
            $lifetime = 60;
        }

        if ($lifetime > 2592000) {
            $lifetime = 2591999;
        }

        return $this->Memcache->set($id, $data, 0, $lifetime);
    }// save


}
