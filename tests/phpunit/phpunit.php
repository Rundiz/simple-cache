<?php


require __DIR__.'/Autoload.php';

$Autoload = new \Rundiz\SimpleCache\Tests\Autoload();
$Autoload->addNamespace('Rundiz\\SimpleCache\\Tests', __DIR__);
$Autoload->addNamespace('Rundiz\\SimpleCache', dirname(dirname(__DIR__)).'/src');
$Autoload->addNamespace('Rundiz\\SimpleCache\\Drivers', dirname(dirname(__DIR__)).'/src/Drivers');
$Autoload->register();

if (is_file(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
}