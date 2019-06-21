<?php
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';


if (!function_exists('apcu_fetch')) {
    die('APCu extension is not installed.');
}
$SimpleCache = new Rundiz\SimpleCache\Drivers\Apcu();

if (isset($_GET['act']) && $_GET['act'] === 'clear') {
    $clearResult = $SimpleCache->clear();
}

// define class for test only.
$TestClass = new stdClass();
$TestClass->saybye = 'Goobye world!';
$TestClass->sayhi = 'Hello world.';
// end class for test.

$dataToTest = [
    [
        'type' => 'String',
        'key' => 'test.cache.string',
        'value' => 'Hello สวัสดี, Goodbye ลาก่อน.',
    ],
    [
        'type' => 'Integer',
        'key' => 'test.cache.int',
        'value' => 1234567890,
    ],
    [
        'type' => 'Float',
        'key' => 'test.cache.float',
        'value' => 9765.32,
    ],
    [
        'type' => 'Boolean',
        'key' => 'test.cache.bool',
        'value' => true,
    ],
    [
        'type' => 'Null',
        'key' => 'test.cache.null',
        'value' => null,
    ],
    [
        'type' => 'Array',
        'key' => 'test.cache.array',
        'value' => ['sayhi' => 'Hello สวัสดี', 'saybye' => 'Goodbye ลาก่อน', 'user' => ['name' => 'Vee W.', 'website' => 'http://rundiz.com']],
    ],
    [
        'type' => 'Object',
        'key' => 'test.cache.object',
        'value' => $TestClass,
    ],
    [
        'type' => 'cacheMultiple',
        'values' => [
            'test.cachemultiple.string' => 'Hello โลก.',
            'test.cachemultiple.object' => $TestClass,
            'test.cachemultiple.array' => ['sayhi' => 'Hello สวัสดี', 'saybye' => 'Goodbye ลาก่อน', 'user' => ['name' => 'Vee W.', 'website' => 'http://rundiz.com'], 'object' => $TestClass],
        ],
    ],
];

unset($TestClass);


/**
 * Running test and display it.
 */
function displayCacheTest(array $item)
{
    global $SimpleCache;

    if ($item['type'] !== 'cacheMultiple') {
        // if NOT cacheMultiple
        if (!isset($_GET['act'])) {
            // no action, test `has()`, `get()`, `set()`, `getMultiple()`, `setMultiple()`.
            echo '<p>The value before any cache.</p>' . PHP_EOL;
            echo '<pre style="background-color: #555; color: white;">' . print_r($item['value'], true) . '</pre>' . PHP_EOL;
            $valueAfterSerializeAndUnserialize = unserialize(serialize($item['value']));

            echo '<h3>Has cache?</h3>' . PHP_EOL;
            echo '<p>' . ($SimpleCache->has($item['key']) ? 'YES' : 'NO') . '<p>' . PHP_EOL;
            echo '<h3>Get cache</h3>' . PHP_EOL;
            $getResult = $SimpleCache->get($item['key']);
            var_dump($getResult);
            if ($SimpleCache->has($item['key']) && $getResult != $valueAfterSerializeAndUnserialize) {
                // if unserialize(serialize(value)) does not match cached value.
                // this compare cannot use === or !== because objects can contain different [number]
                // example: original maybe `object(stdClass)[4]`, cache maybe `object(stdClass)[5]`.
                echo '<p style="color: red;">Cached data was not matched!</p>' . PHP_EOL;
                var_dump($valueAfterSerializeAndUnserialize);
            }
            unset($getResult, $valueAfterSerializeAndUnserialize);

            echo '<h3>Set cache</h3>' . PHP_EOL;
            if (!$SimpleCache->has($item['key'])) {
                var_dump($SimpleCache->set($item['key'], $item['value'], 300));
                echo '<p>Cache was set, please reload the page.</p>' . PHP_EOL;
            } else {
                echo '<p>Cache is already set, no need to set it again.</p>' . PHP_EOL;
            }
        }

        if (isset($_GET['act']) && $_GET['act'] === 'del' && isset($_GET['key'])) {
            echo '<h3>Delete cache</h3>' . PHP_EOL;
            if ($item['key'] === $_GET['key']) {
                var_dump($SimpleCache->delete($_GET['key']));
            }
        }
    } else {
        // if cacheMultiple
        echo '<h3>Cache multiple</h3>' . PHP_EOL;
        echo '<p>The value before any cache.</p>' . PHP_EOL;
        echo '<pre style="background-color: #555; color: white;">' . print_r($item['values'], true) . '</pre>' . PHP_EOL;
        if (!isset($_GET['act'])) {
            echo '<h4>Get multiple</h4>' . PHP_EOL;
            $getResult = $SimpleCache->getMultiple(array_keys($item['values']));
            var_dump($getResult);
            unset($getResult);

            echo '<h4>Set multiple</h4>' . PHP_EOL;
            var_dump($SimpleCache->setMultiple($item['values'], 600));
        }

        if (isset($_GET['act']) && $_GET['act'] === 'del' && isset($_GET['key']) && $_GET['key'] === 'cacheMultiple') {
            echo '<h4>Delete multiple</h4>' . PHP_EOL;
            var_dump($SimpleCache->deleteMultiple(array_keys($item['values'])));
        }
    }// endif; type

    $item = [];
}// displayCacheTest

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Simple Cache tests.</title>
    </head>
    <body>
        <h1>APCu</h1>
        <p><a href="./">Go back</a> | <a href="?act=clear">Clear all cache</a></p>
        <?php 
        if (isset($clearResult)) {
            echo 'clear result: ';
            var_dump($clearResult);
        } 
        ?> 
        <hr>
        <?php
        foreach ($dataToTest as $key => $item) {
            echo '<h2>' . $item['type'] . ' &nbsp; <small><a href="?act=del&amp;key=' . (isset($item['key']) ? htmlspecialchars($item['key'], ENT_QUOTES) : 'cacheMultiple') . '">delete</a></small></h2>' . PHP_EOL;
            displayCacheTest($item);
            echo '<hr>' . PHP_EOL;
        }// endforeach;
        unset($item, $key);
        ?> 
        <h2>Not exists cache</h2>
        <p>Test how if you get not exists cache. (default value is <code>array('not' =&gt; 'exists');</code>)</p>
        <?php var_dump($SimpleCache->get('something.notexists', ['not' => 'exists'])); ?> 
    </body>
</html>