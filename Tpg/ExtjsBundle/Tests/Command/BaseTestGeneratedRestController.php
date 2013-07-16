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
            new CreateDatabaseDoctrineCommand(),
            new CreateSchemaDoctrineCommand(),
            new CreateSchemaDoctrineODMCommand(),
        ));
        $kernel->boot();
        $command = $app->find('generate:rest:controller');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--controller' => 'TestTestBundle:Car',
            '--entity' => 'TestTestBundle:Car'
        ), array('interactive'=>false));
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--controller' => 'TestTestBundle:Order',
            '--entity' => 'TestTestBundle:Order',
            '--mongo' => true,
        ), array('interactive'=>false));
        $command = $app->find('doctrine:database:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));
        $command = $app->find('doctrine:schema:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));
        $command = $app->find('doctrine:mongodb:schema:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));
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
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new DropDatabaseDoctrineCommand(),
            new DropSchemaDoctrineODMCommand(),
        ));
        $kernel->boot();
        $command = $app->find('doctrine:database:drop');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--force' => true,
        ));
        $command = $app->find('doctrine:mongodb:schema:drop');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));
        $kernel->shutdown();
    }
}