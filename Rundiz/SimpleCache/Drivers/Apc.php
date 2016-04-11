<?php
/**
 * PHP Simple Cache Apc driver.
 * 
 * @package Simple Cache
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT
 * 
 */


namespace Rundiz\SimpleCache\Drivers;

use Rundiz\SimpleCache\SimpleCacheInterface;

/**
 * APC driver class.
 *
 * @since 2.0
 */
class Apc implements SimpleCacheInterface
{


    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return apc_clear_cache() && apc_clear_cache('user');
    }// clear


    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        if (!apc_exists($id)) {
            return false;
        }

        return apc_delete($id);
    }// delete


    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        return apc_fetch($id);
    }// get


    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifetime = 60)
    {
        if (!is_int($lifetime) || is_int($lifetime) && $lifetime <= 0) {
            $lifetime = 60;
        }

        return apc_store($id, $data, $lifetime);
    }// save


}
