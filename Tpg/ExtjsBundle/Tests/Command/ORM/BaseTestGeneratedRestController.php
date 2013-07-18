<?php
namespace Tpg\ExtjsBundle\Tests\Command\ORM;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Router;
use Test\TestBundle\Entity\Car;
use Symfony\Bundle\FrameworkBundle\Client;
use Tpg\ExtjsBundle\Tests\Command\BaseTestGeneratedRestController as Base;

class BaseTestGeneratedRestController extends Base {
    /** @var Car[] $records */
    protected $records = array();
    /** @var Client */
    protected $client;

    protected function setUp() {
        parent::setUp();
        $client = $this->createClient();
        /** @var EntityManager $manager */
        $manager = $client->getContainer()->get("doctrine.orm.default_entity_manager");
        $toyota = new Car();
        $toyota->setName("Toyota")
            ->setPlateNumber("KJ342");
        $manager->persist($toyota);
        $car = new Car();
        $car->setName("Ford")
            ->setPlateNumber("AA123")
            ->addRelatedCar($toyota);
        $manager->persist($car);
        $this->records[] = $car;
        $car = new Car();
        $car->setName("Honda")
            ->setPlateNumber("BB243");
        $manager->persist($car);
        $this->records[] = $car;
        $this->records[] = $toyota;
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
        /** @var EntityManager $manager */
        $manager = $this->createClient()->getContainer()->get("doctrine.orm.default_entity_manager");
        $manager->createQueryBuilder()->delete('Test\TestBundle\Entity\Car', 'c')->getQuery()->execute();
        parent::tearDown();
    }

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new CreateDatabaseDoctrineCommand(),
            new CreateSchemaDoctrineCommand(),
        ));
        $kernel->boot();
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
        $kernel->shutdown();
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new DropDatabaseDoctrineCommand(),
        ));
        $kernel->boot();
        $command = $app->find('doctrine:database:drop');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--force' => true,
        ));
        $kernel->shutdown();
    }
}