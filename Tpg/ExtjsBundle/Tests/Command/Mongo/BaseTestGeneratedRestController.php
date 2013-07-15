<?php
namespace Tpg\ExtjsBundle\Tests\Command\Mongo;

use Doctrine\Bundle\MongoDBBundle\Command\CreateSchemaDoctrineODMCommand;
use Doctrine\Bundle\MongoDBBundle\Command\DropSchemaDoctrineODMCommand;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Router;
use Test\TestBundle\Document\Order;
use Test\TestBundle\Document\OrderLineItem;
use Test\TestBundle\Entity\Car;
use Symfony\Bundle\FrameworkBundle\Client;
use Tpg\ExtjsBundle\Command\GenerateRestControllerCommand;

class BaseTestGeneratedRestController extends WebTestCase {
    /** @var Car[] $records */
    protected $records = array();
    /** @var Client */
    protected $client;

    public static function setUpBeforeClass() {
        @unlink(__DIR__.'/../../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml');
        @unlink(__DIR__.'/../../Fixtures/Test/TestBundle/Controller/OrderController.php');
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new GenerateRestControllerCommand(),
            new CreateSchemaDoctrineODMCommand(),
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
        $command = $app->find('doctrine:mongodb:schema:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));
        $kernel->shutdown();
        @unlink(__DIR__.'/../../app/cache/test/appTestUrlGenerator.php.meta');
        @unlink(__DIR__.'/../../app/cache/test/appTestUrlGenerator.php');
        @unlink(__DIR__.'/../../app/cache/test/appTestUrlMatcher.php.meta');
        @unlink(__DIR__.'/../../app/cache/test/appTestUrlMatcher.php');
    }

    public static function tearDownAfterClass() {
        @unlink(__DIR__.'/../../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml');
        @unlink(__DIR__.'/../../Fixtures/Test/TestBundle/Controller/OrderController.php');
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new DropSchemaDoctrineODMCommand(),
        ));
        $kernel->boot();
        $command = $app->find('doctrine:mongodb:schema:drop');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--force' => true,
        ));
        $kernel->shutdown();
    }

    protected function setUp() {
        parent::setUp();
        $client = $this->createClient();
        /** @var DocumentManager $manager */
        $manager = $client->getContainer()->get("doctrine.odm.mongodb.document_manager");
        $order = new Order();
        $order->setName("Invoice 1")
            ->setTotalPrice(5.02)
            ->addLineItem(
                OrderLineItem::newInstance()
                    ->setProductId(1)
                    ->setQuantity(1)
                    ->setPrice(5.02)
                    ->setTotal(5.02)
            );
        $manager->persist($order);
        $this->records[] = $order;
        $order = new Order();
        $order->setName("Invoice 2")
            ->setTotalPrice(10.58)
            ->addLineItem(
                OrderLineItem::newInstance()
                    ->setProductId(2)
                    ->setQuantity(2)
                    ->setPrice(2.50)
                    ->setTotal(5.00)
            )
            ->addLineItem(
                OrderLineItem::newInstance()
                    ->setProductId(3)
                    ->setQuantity(1)
                    ->setPrice(5.58)
                    ->setTotal(5.58)
            );
        $manager->persist($order);
        $this->records[] = $order;
        $manager->flush();
        /** @var RestYamlCollectionLoader $loader */
        $loader = $client->getContainer()->get("fos_rest.routing.loader.yaml_collection");
        $router = $client->getContainer()->get('router');
        $router->getRouteCollection()->addCollection(
            $loader->load(__DIR__.'/../../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml')
        );
        $this->client = $client;
    }

    protected function tearDown() {
        /** @var DocumentManager $manager */
        $manager = $this->createClient()->getContainer()->get("doctrine.odm.mongodb.document_manager");
        $manager->createQueryBuilder()->remove('Test\TestBundle\Document\Order')->getQuery()->execute();
        parent::tearDown();
    }
}