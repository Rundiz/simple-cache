<?php
require dirname(dirname(__DIR__)).'/Rundiz/SimpleCache/SimpleCacheInterface.php';
require dirname(dirname(__DIR__)).'/Rundiz/SimpleCache/Drivers/Apc.php';

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
        ($get_action == 'delete_string' && gettype($cache_value) == 'string') ||
        ($get_action == 'delete_int' && gettype($cache_value) == 'integer') ||
        ($get_action == 'delete_float' && gettype($cache_value) == 'double')
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
        <h1>Test basic variable types.</h1>
        <p><a href="./">Go back</a> | <a href="?act=clear">Clear all cache</a></p>
        <hr>
        <h2>String <small><a href="?act=delete_string">delete</a></small></h2>
        <?php
        
        global $get_action;
        $get_action = (isset($_GET['act']) ? $_GET['act'] : '');
        global $SimpleCache;
        $SimpleCache = new \Rundiz\SimpleCache\Drivers\Apc();

        $cache_name = 'test.string.var.type.test_cache_string';
        displayCacheTest($cache_name, 'Hello World'."\n".'Good bye then.'."\n\n".'สวัสดี'."\n".'ลาก่อน'."\n");
        ?> 

        <h2>Integer <small><a href="?act=delete_int">delete</a></small></h2>
        <?php
        $cache_name = 'test_cache_int';
        displayCacheTest($cache_name, 1234567);
        ?> 

        <h2>Float <small><a href="?act=delete_float">delete</a></small></h2>
        <?php
        $cache_name = 'test_cache_foat';
        displayCacheTest($cache_name, floatval(12345.67));
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