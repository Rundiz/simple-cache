<?php


namespace Rundiz\SimpleCache\Tests;

class CacheTest extends \PHPUnit\Framework\TestCase
{


    public function testApcCache()
    {
        if (!function_exists('apc_fetch')) {
            return $this->markTestSkipped('APC cache is not installed.');
        }

        if (apc_store('test_apc_cli', 'is_enable') === false && apc_fetch('test_apc_cli') === false && apc_delete('test_apc_cli') === false) {
            return $this->markTestSkipped('APC cache is not enabled for CLI.');
        }

        $SimpleCache = new \Rundiz\SimpleCache\Drivers\Apc();
        $save_result = $SimpleCache->save('cache_id', array('cache_data' => 'cache value'));

        // Assert
        $this->assertTrue($save_result);
        $this->assertArrayHasKey('cache_data', $SimpleCache->get('cache_id'));

        $delete_result = $SimpleCache->delete('cache_id');
        $this->assertTrue($delete_result);

        $clear_result = $SimpleCache->clear();
        $this->assertTrue($clear_result);
    }// testApcCache


    public function testApcuCache()
    {
        if (!function_exists('apcu_fetch')) {
            return $this->markTestSkipped('APCu cache is not installed.');
        }
        $SimpleCache = new \Rundiz\SimpleCache\Drivers\Apcu();
        $save_result = $SimpleCache->save('cache_id', array('cache_data' => 'cache value'));

        // Assert
        $this->assertTrue($save_result);
        $this->assertArrayHasKey('cache_data', $SimpleCache->get('cache_id'));

        $delete_result = $SimpleCache->delete('cache_id');
        $this->assertTrue($delete_result);

        $clear_result = $SimpleCache->clear();
        $this->assertTrue($clear_result);
    }// testApcuCache
    
    
    public function testMemcacheCache()
    {
        if (!class_exists('\\Memcache')) {
            return $this->markTestSkipped('Memcache is not installed.');
        }
        $memcache = new \Memcache;
        $memcache->connect('localhost', 11211);
        $SimpleCache = new \Rundiz\SimpleCache\Drivers\Memcache($memcache);
        $save_result = $SimpleCache->save('cache_id', array('cache_data' => 'cache value'));

        // Assert
        $this->assertTrue($save_result);
        $this->assertArrayHasKey('cache_data', $SimpleCache->get('cache_id'));

        $delete_result = $SimpleCache->delete('cache_id');
        $this->assertTrue($delete_result);

        $clear_result = $SimpleCache->clear();
        $this->assertTrue($clear_result);
    }// testMemcacheCache
    
    
    public function testMemcachedCache()
    {
        if (!class_exists('\\Memcached')) {
            return $this->markTestSkipped('Memcached is not installed.');
        }
        $memcache = new \Memcached;
        $memcache->addServer('localhost', 11211);
        $SimpleCache = new \Rundiz\SimpleCache\Drivers\Memcached($memcache);
        $save_result = $SimpleCache->save('cache_id', array('cache_data' => 'cache value'));

        // Assert
        $this->assertTrue($save_result);
        $this->assertArrayHasKey('cache_data', $SimpleCache->get('cache_id'));

        $delete_result = $SimpleCache->delete('cache_id');
        $this->assertTrue($delete_result);

        $clear_result = $SimpleCache->clear();
        $this->assertTrue($clear_result);
    }// testMemcachedCache


    public function testFileSystemCache()
    {
        $SimpleCache = new \Rundiz\SimpleCache\Drivers\FileSystem();
        $save_result = $SimpleCache->save('cache_id', array('cache_data' => 'cache value'));

        // Assert
        $this->assertTrue($save_result);
        $this->assertArrayHasKey('cache_data', $SimpleCache->get('cache_id'));

        $delete_result = $SimpleCache->delete('cache_id');
        $this->assertTrue($delete_result);

        $clear_result = $SimpleCache->clear();
        $this->assertTrue($clear_result);
    }// testFileSystemCache


}
