<?php
/**
 * PHP Simple Cache Memcached driver.
 * 
 * @package Simple Cache
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT
 * 
 */


namespace Rundiz\SimpleCache\Drivers;

use Rundiz\SimpleCache\SimpleCacheInterface;

/**
 * Memcached driver class
 *
 * @since 2.0
 */
class Memcached implements SimpleCacheInterface
{


    /**
     * Memcached class.
     * @var \Memcached
     */
    protected $Memcached;


    /**
     * Class constructor.
     * 
     * @param \Memcached $memcached Memcached class.
     */
    public function __construct(\Memcached $memcached)
    {
        $this->Memcached = $memcached;
    }// __construct


    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->Memcached->flush();
    }// clear


    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        return $this->Memcached->delete($id) || $this->Memcached->getResultCode() === \Memcached::RES_NOTFOUND;
    }// delete


    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        return $this->Memcached->get($id);
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

        return $this->Memcached->set($id, $data, $lifetime);
    }// save


}
