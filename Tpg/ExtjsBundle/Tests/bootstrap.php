<?php

$file = __DIR__.'/../../../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

$loader = require_once $file;

use \Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\ClassLoader\UniversalClassLoader;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

AnnotationRegistry::registerAutoloadNamespace('Test', __DIR__.'/Fixtures/');

$loader = new UniversalClassLoader();
$loader->registerNamespace('Test', __DIR__.'/Fixtures/');
$loader->register();