<?php
/**
 * PHP Simple Cache Apcu driver.
 * 
 * @package Simple Cache
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT
 * 
 */


namespace Rundiz\SimpleCache\Drivers;

use Rundiz\SimpleCache\SimpleCacheInterface;

/**
 * APCu driver class.
 *
 * @since 2.0
 */
class Apcu implements SimpleCacheInterface
{


    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return apcu_clear_cache();
    }// clear


    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        if (!apcu_exists($id)) {
            return false;
        }

        return apcu_delete($id);
    }// delete


    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        return apcu_fetch($id);
    }// get


    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifetime = 60)
    {
        if (!is_int($lifetime) || is_int($lifetime) && $lifetime <= 0) {
            $lifetime = 60;
        }

        return apcu_store($id, $data, $lifetime);
    }// save


}
