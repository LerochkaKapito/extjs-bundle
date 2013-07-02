<?php

if (is_file(__DIR__.$_SERVER['PHP_SELF'])) {
    echo file_get_contents(__DIR__.$_SERVER['PHP_SELF']);
    exit(0);
}

$loader = require_once __DIR__.'/bootstrap.php';
require_once __DIR__.'/app/AppKernel.php';
use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('test', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);