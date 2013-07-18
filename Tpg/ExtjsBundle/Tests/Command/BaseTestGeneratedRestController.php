<?php
namespace Tpg\ExtjsBundle\Tests\Command;

include_once(__DIR__.'/../app/AppKernel.php');

use Doctrine\Bundle\MongoDBBundle\Command\CreateSchemaDoctrineODMCommand;
use Doctrine\Bundle\MongoDBBundle\Command\DropSchemaDoctrineODMCommand;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Router;
use Tpg\ExtjsBundle\Command\GenerateRestControllerCommand;

class BaseTestGeneratedRestController extends WebTestCase {
    public static function setUpBeforeClass() {
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml');
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Controller/CarController.php');
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Controller/OrderController.php');
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new GenerateRestControllerCommand(),
        ));
        $kernel->boot();
        $command = $app->find('generate:rest:controller');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--controller' => 'TestTestBundle:Order',
            '--entity' => 'TestTestBundle:Order',
            '--mongo' => true,
        ), array('interactive'=>false));
        $kernel->shutdown();
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new GenerateRestControllerCommand(),
        ));
        $kernel->boot();
        $command = $app->find('generate:rest:controller');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--controller' => 'TestTestBundle:Car',
            '--entity' => 'TestTestBundle:Car'
        ), array('interactive'=>false));
        $kernel->shutdown();
        @unlink(__DIR__.'/../app/cache/test/appTestUrlGenerator.php.meta');
        @unlink(__DIR__.'/../app/cache/test/appTestUrlGenerator.php');
        @unlink(__DIR__.'/../app/cache/test/appTestUrlMatcher.php.meta');
        @unlink(__DIR__.'/../app/cache/test/appTestUrlMatcher.php');
    }

    public static function tearDownAfterClass() {
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml');
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Controller/CarController.php');
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Controller/OrderController.php');
    }
}