<?php
namespace Tpg\ExtjsBundle\Tests\Command;

include_once(__DIR__.'/../app/AppKernel.php');

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Router;
use Test\TestBundle\Entity\Car;
use Tpg\ExtjsBundle\Command\GenerateRestControllerCommand;

class GeneratedRestControllerTest extends WebTestCase {

    protected $records = array();
    protected $client;

    public static function setUpBeforeClass() {
        unlink(__DIR__.'/../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml');
        unlink(__DIR__.'/../Fixtures/Test/TestBundle/Controller/CarController.php');
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->addCommands(array(
            new GenerateRestControllerCommand(),
            new CreateDatabaseDoctrineCommand(),
            new CreateSchemaDoctrineCommand(),
        ));
        $kernel->boot();
        $command = $app->find('generate:rest:controller');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--controller' => 'TestTestBundle:Car',
            '--entity' => 'TestTestBundle:Car'
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
        $kernel->shutdown();
    }

    public static function tearDownAfterClass() {
        unlink(__DIR__.'/../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml');
        unlink(__DIR__.'/../Fixtures/Test/TestBundle/Controller/CarController.php');
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

    protected function setUp() {
        parent::setUp();
        $client = $this->createClient();
        /** @var EntityManager $manager */
        $manager = $client->getContainer()->get("doctrine.orm.default_entity_manager");
        $car = new Car();
        $car->setName("Ford")
            ->setPlateNumber("AA123");
        $manager->persist($car);
        $this->records[] = $car;
        $car = new Car();
        $car->setName("Honda")
            ->setPlateNumber("BB243");
        $manager->persist($car);
        $this->records[] = $car;
        $manager->flush();
        /** @var RestYamlCollectionLoader $loader */
        $loader = $client->getContainer()->get("fos_rest.routing.loader.yaml_collection");
        $router = $client->getContainer()->get('router');
        $router->getRouteCollection()->addCollection(
            $loader->load(__DIR__.'/../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml')
        );
        $this->client = $client;
    }

    protected function tearDown() {
        /** @var EntityManager $manager */
        $manager = $this->createClient()->getContainer()->get("doctrine.orm.default_entity_manager");
        $manager->createQueryBuilder()->delete('Test\TestBundle\Entity\Car', 'c')->getQuery()->execute();
        parent::tearDown();
    }

    public function testGetsAction() {
        $this->client->request('GET', '/cars.json');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
    }
}