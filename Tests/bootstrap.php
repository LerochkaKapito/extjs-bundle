<?php

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

$loader = require($file);

use \Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\ClassLoader\UniversalClassLoader;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

AnnotationRegistry::registerAutoloadNamespace('Test', __DIR__.'/Fixtures/');

/**
 * AnnotationRegistry need some help finding annotation class.
 */
$loader->addClassMap(array(
    'Tpg\ExtjsBundle\Annotation\Model'=>__DIR__.'/../Annotation/Model.php',
    'Tpg\ExtjsBundle\Annotation\Direct'=>__DIR__.'/../Annotation/Direct.php',
    'Tpg\ExtjsBundle\Annotation\ModelProxy'=>__DIR__.'/../Annotation/ModelProxy.php',
));

$loader = new UniversalClassLoader();
$loader->registerNamespace('Test', __DIR__.'/Fixtures/');
$loader->register();