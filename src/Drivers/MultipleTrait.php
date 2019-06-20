<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Drivers;


use Rundiz\SimpleCache\Exceptions\InvalidArgumentException;


/**
 * Multiple trait contain methods that work on `xxxMultiple()` such as `getMultiple()`.
 * 
 * @since 3.0
 */
trait MultipleTrait
{


    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys): bool
    {
        if (!is_array($keys) && !$keys instanceof Traversable) {
            throw new InvalidArgumentException('keys must be either of type array or Traversable');
        }

        $result = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                if ($result === true) {
                    // if there was AN error, return false.
                    $result = false;
                }
            }
        }
        unset($key);

        return $result;
    }// deleteMultiple


    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys) && !$keys instanceof Traversable) {
            throw new InvalidArgumentException('keys must be either of type array or Traversable');
        }

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        unset($key);

        return $result;
    }// getMultiple


    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null): bool
    {
        if (!is_array($values) && !$values instanceof Traversable) {
            throw new InvalidArgumentException('keys must be either of type array or Traversable');
        }

        $result = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                if ($result === true) {
                    // if there was AN error, return false.
                    $result = false;
                }
            }
        }

        return $result;
    }// setMultiple


}
