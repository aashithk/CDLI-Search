<?php
session_start();

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";


$classLoader = new \Doctrine\Common\ClassLoader('DoctrineExtensions', "vendor/doctrine/DoctrineExtensions/lib");
$classLoader->register();

foreach (glob("entities/*.php") as $filename) {
    require_once $filename;
}
foreach (glob("utils/*.php") as $filename) {
    require_once $filename;
}
require_once "renderers/ObjectRenderer.php";
foreach (glob("renderers/*.php") as $filename) {
    require_once $filename;
}

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = false;
$config = Setup::createXMLMetadataConfiguration(array(__DIR__ . "/config"), $isDevMode);
// only hydrate these tables
$config->setFilterSchemaAssetsExpression("/(^fulltrans$)|(^cataloguesnew$)|(^revhistories$)|(^webuser$)|(^lockforModify)/");
// add custom function for REGEX
$config->addCustomNumericFunction("REGEXP", 'DoctrineExtensions\Query\Mysql\Regexp');
$config->addCustomNumericFunction("MATCH", 'DoctrineExtensions\Query\Mysql\MatchAgainst');

// cache
// Don't turn on cache, otherwise there will be deadlock...

$dbParams = array(
    'driver' => 'pdo_mysql',
    'user' => 'cdliuser',
    'password' => 'chA@efeb',
    'dbname' => 'cdlidb',
    'host' => 'db.cdli.ucla.edu',
    'charset' => 'utf8',
    'driverOptions' => array(
        1002 => 'SET NAMES utf8'
    )
);
// obtaining the entity manager
$entityManager = EntityManager::create($dbParams, $config);


// function for getting entityManager
function getEM()
{
    global $entityManager;
    return $entityManager;
}

