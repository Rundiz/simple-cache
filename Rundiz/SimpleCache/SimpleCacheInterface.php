<?php
/**
 * PHP Simple Cache interface.
 * 
 * @package Simple Cache
 * @version 2.0
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT
 */


namespace Rundiz\SimpleCache;

/**
 * Interface for cache drivers.
 * 
 * @since 2.0
 */
interface SimpleCacheInterface
{


    /**
     * Clear all cache data.
     * 
     * @return boolean Return true if cleared successfully. Return false for otherwise.
     */
    public function clear();


    /**
     * Delete a cache entry.
     * 
     * @param string $id The cache id.
     * @return boolean Return true if cleared successfully. Return false for otherwise.
     */
    public function delete($id);


    /**
     * Get a cache entry.
     * 
     * @param string $id The id of the cache entry to get.
     * @return mixed Return the cached data or return false if that cache id is not exists.
     */
    public function get($id);


    /**
     * Save data into the cache.
     * 
     * @param string $id The cache id.
     * @param mixed $data The cache data.
     * @param integer $lifetime The life time in seconds for this cache. If set to anything else less than 1, it will be automatically set to 60 by default.
     * @return boolean Return true on successfully saved data to the cache, return false for otherwise.
     */
    public function save($id, $data, $lifetime = 60);


}
