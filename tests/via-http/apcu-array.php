<?php
require dirname(dirname(__DIR__)).'/Rundiz/SimpleCache/SimpleCacheInterface.php';
require dirname(dirname(__DIR__)).'/Rundiz/SimpleCache/Drivers/Apcu.php';

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
        ($get_action == 'delete_array2' && $cache_name == 'test.array2') ||
        ($get_action == 'delete_array3' && $cache_name == 'test.array3')
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
        <h1>Test variable type array.</h1>
        <p><a href="./">Go back</a> | <a href="?act=clear">Clear all cache</a></p>
        <hr>
        <h2>2D array <small><a href="?act=delete_array2">delete</a></small></h2>
        <?php
        
        global $get_action;
        $get_action = (isset($_GET['act']) ? $_GET['act'] : '');
        global $SimpleCache;
        $SimpleCache = new \Rundiz\SimpleCache\Drivers\Apcu();

        $cache_name = 'test.array2';
        displayCacheTest($cache_name, array('Hi world', 'Bye world'));
        ?> 

        <h2>Multi dimension array <small><a href="?act=delete_array3">delete</a></small></h2>
        <?php
        $cache_name = 'test.array3';
        displayCacheTest($cache_name, array('sayhi' => 'Hello World', 'saybye' => 'Bye Bye!', 'user' => array('name' => 'John Wick', 'carreer' => 'Kiler', 'skill' => array('str' => 10, 'agi' => 9, 'dex' => 7))));
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