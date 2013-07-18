<?php
namespace Tpg\ExtjsBundle\Tests\Command\ODM;

use Doctrine\Bundle\MongoDBBundle\Command\CreateSchemaDoctrineODMCommand;
use Doctrine\Bundle\MongoDBBundle\Command\DropSchemaDoctrineODMCommand;
use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Router;
use Test\TestBundle\Document\Order;
use Test\TestBundle\Document\OrderLineItem;
use Symfony\Bundle\FrameworkBundle\Client;
use Tpg\ExtjsBundle\Tests\Command\BaseTestGeneratedRestController as Base;

class BaseTestGeneratedRestController extends Base {
    /** @var Order[] $records */
    protected $records = array();
    /** @var \Test\TestBundle\Document\Client $clientDocument */
    protected $clientDocument;
    /** @var Client */
    protected $client;

    protected function setUp() {
        parent::setUp();
        $client = $this->createClient();
        /** @var DocumentManager $manager */
        $manager = $client->getContainer()->get("doctrine.odm.mongodb.document_manager");
        $clientDocument = new \Test\TestBundle\Document\Client();
        $clientDocument->setFirstName('Test')
            ->setLastName('Test');
        $manager->persist($clientDocument);
        $this->clientDocument = new \Test\TestBundle\Document\Client();
        $this->clientDocument->setFirstName('Jimmy')
            ->setLastName('Bob');
        $manager->persist($this->clientDocument);
        $order = new Order();
        $order->setName("Invoice 1")
            ->setTotalPrice(5.02)
            ->addLineItem(
                OrderLineItem::newInstance()
                    ->setProductId(1)
                    ->setQuantity(1)
                    ->setPrice(5.02)
                    ->setTotal(5.02)
            )
            ->setClient($clientDocument)
        ;
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
            )
            ->setClient($clientDocument)
        ;
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
        $manager->createQueryBuilder()->remove('Test\TestBundle\Document\Client')->getQuery()->execute();
        parent::tearDown();
    }

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new CreateSchemaDoctrineODMCommand(),
        ));
        $kernel->boot();
        $command = $app->find('doctrine:mongodb:schema:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));
        $kernel->shutdown();
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
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
            ));
        $kernel->shutdown();
    }
}