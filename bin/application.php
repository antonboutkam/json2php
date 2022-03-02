<?php

use Hurah\Generators\Command\CollectionCommand;
use Hurah\Generators\Command\Json2PhpCommand;
use Symfony\Component\Console\Application;

ini_set('display_errors', 1);
error_reporting(E_ALL);

$sRoot = dirname(__DIR__, 1);
require_once $sRoot . '/vendor/autoload.php';

try
{
    $oApplication = new Application('meubelmens.nl');
    $oApplication->add(new Json2PhpCommand());
    $oApplication->add(new CollectionCommand());

    $oApplication->run();
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;
    echo $e->getFile();
    echo "aaaaaaaaaaaaaaa";
}
