<?php
require dirname(dirname(__DIR__)).'/Rundiz/SimpleCache/SimpleCacheInterface.php';
require dirname(dirname(__DIR__)).'/Rundiz/SimpleCache/Drivers/FileSystem.php';

function displayCacheTest($cache_name, $cache_value)
{
    global $get_action;
    global $SimpleCache;
    
    if ($get_action !== 'clear') {
        $test_get = $SimpleCache->get($cache_name);
        echo '<p>Get the cache data:</p>';
        echo '<pre>';
        var_dump($test_get);
        echo '</pre>';
        unset($test_get);

        $test_save = $SimpleCache->save($cache_name, $cache_value);
        echo '<p>Save cache data: '.gettype($test_save).' ('.var_export($test_save, true).')</p>';
        unset($test_save);

        $test_get = $SimpleCache->get($cache_name);
        echo '<p>Get the cache data <strong>again</strong>:</p>';
        echo '<pre>';
        var_dump($test_get);
        echo '</pre>';
        unset($test_get);
    }

    if (
        ($get_action == 'delete_obj1' && $cache_name == 'test.obj1') ||
        ($get_action == 'delete_obj2' && $cache_name == 'test.obj2')
    ) {
        $test_delete = $SimpleCache->delete($cache_name);
        echo '<p>Delete cache data: '.gettype($test_delete).' ('.var_export($test_delete, true).')</p>';
        unset($test_delete);

        $test_get = $SimpleCache->get($cache_name);
        echo '<p>Get the cache data <strong>again after delete</strong>:</p>';
        echo '<pre>';
        var_dump($test_get);
        echo '</pre>';
        unset($test_get);
    }
}// displayCacheTest
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Simple Cache tests.</title>
    </head>
    <body>
        <h1>Test variable type object.</h1>
        <p><a href="./">Go back</a> | <a href="?act=clear">Clear all cache</a></p>
        <hr>
        <h2>Test object 1 <small><a href="?act=delete_obj1">delete</a></small></h2>
        <?php
        
        global $get_action;
        $get_action = (isset($_GET['act']) ? $_GET['act'] : '');
        global $SimpleCache;
        $SimpleCache = new \Rundiz\SimpleCache\Drivers\FileSystem();

        $cache_name = 'test.obj1';
        $obj = new \stdClass();
        $obj->sayhi = 'Hi world';
        $obj->bye = 'Bye world';
        displayCacheTest($cache_name, $obj);
        unset($obj);
        ?> 

        <h2>Test object 2 <small><a href="?act=delete_obj2">delete</a></small></h2>
        <?php
        $cache_name = 'test.obj2';
        class TestClass
        {
            public $sayhi = 'Hello World';
            public $saybye = 'Bye Bye!';
            public function user()
            {
                return array(
                    'name' => 'John Wick', 
                    'carreer' => 'Kiler', 
                    'skill' => array('str' => 10, 'agi' => 9, 'dex' => 7),
                );
            }
        }
        $obj = new \TestClass();
        $obj->user();
        displayCacheTest($cache_name, $obj);
        unset($obj);
        ?> 

        <?php
        if ($get_action == 'clear') {
            echo '<h2>Clear cache</h2>';
            $test_delete = $SimpleCache->clear();
            echo '<p>Test clear all cache data:</p>';
            var_dump($test_delete);
            unset($test_delete);
            echo '<p>You can check cleared cache by click back and reload this page.</p>';
        }
        ?> 
    </body>
</html>