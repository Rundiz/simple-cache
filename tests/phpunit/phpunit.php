<?php


require __DIR__.'/Autoload.php';

$Autoload = new \Rundiz\SimpleCache\Tests\Autoload();
$Autoload->addNamespace('Rundiz\\SimpleCache\\Tests', __DIR__);
$Autoload->addNamespace('Rundiz\\SimpleCache', dirname(dirname(__DIR__)).'/Rundiz/SimpleCache');
$Autoload->addNamespace('Rundiz\\SimpleCache\\Drivers', dirname(dirname(__DIR__)).'/Rundiz/SimpleCache/Drivers');
$Autoload->register();