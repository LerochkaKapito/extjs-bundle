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
use Symfony\Bundle\FrameworkBundle\Client;
use Tpg\ExtjsBundle\Command\GenerateRestControllerCommand;

class GeneratedRestControllerTest extends WebTestCase {

    protected $records = array();
    /** @var Client */
    protected $client;

    public static function setUpBeforeClass() {
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml');
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Controller/CarController.php');
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
        @unlink(__DIR__.'/../app/cache/test/appTestUrlGenerator.php.meta');
        @unlink(__DIR__.'/../app/cache/test/appTestUrlGenerator.php');
        @unlink(__DIR__.'/../app/cache/test/appTestUrlMatcher.php.meta');
        @unlink(__DIR__.'/../app/cache/test/appTestUrlMatcher.php');
    }

    public static function tearDownAfterClass() {
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Resources/config/routing.rest.yml');
        @unlink(__DIR__.'/../Fixtures/Test/TestBundle/Controller/CarController.php');
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
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($content));
    }

    public function testGetsActionWithFilterOnName() {
        $filter = json_encode(array(
            array('property'=>'name','value'=>'Ford')
        ));
        $this->client->request('GET', '/cars.json?filter='.$filter);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
        $this->assertEquals('AA123', $content[0]['plateNumber']);
    }

    public function testGetsActionWithFilterOnPlateNumner() {
        $filter = json_encode(array(
            array('property'=>'plateNumber','value'=>'BB243')
        ));
        $this->client->request('GET', '/cars.json?filter='.$filter);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
    }

    public function testGetsActionWithFilterOnBothReturnNothing() {
        $filter = json_encode(array(
            array('property'=>'name', 'value'=>'Ford'),
            array('property'=>'plateNumber', 'value'=>'BB243')
        ));
        $this->client->request('GET', '/cars.json?filter='.$filter);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(0, count($content));
    }

    public function testGetsActionWithSortASC() {
        $sort = json_encode(array(
            array('property'=>'name', 'direction'=>'ASC')
        ));
        $this->client->request('GET', '/cars.json?sort='.$sort);
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Ford', $content[0]['name']);
        $this->assertEquals('Honda', $content[1]['name']);
    }

    public function testGetsActionWithSortDESC() {
        $sort = json_encode(array(
            array('property'=>'name', 'direction'=>'DESC')
        ));
        $this->client->request('GET', '/cars.json?sort='.$sort);
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Ford', $content[1]['name']);
        $this->assertEquals('Honda', $content[0]['name']);
    }

    public function testGetsActionWithStart() {
        $this->client->request('GET', '/cars.json?start=1');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
    }

    public function testGetsActionWithLimit() {
        $this->client->request('GET', '/cars.json?limit=1');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
    }

    public function testGetsActionWithPage() {
        $this->client->request('GET', '/cars.json?page=2');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(0, count($content));
        $this->client->request('GET', '/cars.json?page=2&limit=1');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
    }

    public function testGetActionWithId() {
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $cars = $repo->findAll();
        /** @var Car $car */
        $car = $cars[0];
        $this->client->request('GET', '/cars/'.$car->getId().'.json');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($car->getId(), $content['id']);
        $this->assertEquals($car->getName(), $content['name']);
        $this->assertEquals($car->getPlateNumber(), $content['plateNumber']);
    }

    public function testPostAction() {
        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
            'name'=>'BMW',
            'plateNumber'=>'ZZ1267',
        )));
        $this->assertEquals("201", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $this->assertEquals(
            3, count($repo->findAll())
        );
        $record = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('BMW', $record['name']);
        $this->assertEquals('ZZ1267', $record['plateNumber']);
    }

    public function testPostActionWithJMSGroup() {
        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
            'name'=>'BMW',
            'plateNumber'=>'ZZ1267',
            'password'=>'xxx',
        )));
        $this->assertEquals("201", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $record = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNull($record['password']);
        $this->assertEquals('xxx', $repo->find($record['id'])->getPassword());
    }

    public function testPostActionWithError() {
        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
            'name'=>'BMW',
        )));
        $this->assertEquals("400", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }

    public function testPutAction() {
        $record = $this->records[0];
        $this->client->request('PUT', '/cars/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Mazda',
            'plateNumber'=>'AA00',
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $this->assertEquals(
            2, count($repo->findAll())
        );
        $record = $repo->find($record->getId());
        $this->assertEquals('Mazda', $record->getName());
        $this->assertEquals('AA00', $record->getPlateNumber());
    }

    public function testPutActionWithSomeEmpty() {
        $record = $this->records[0];
        $this->client->request('PUT', '/cars/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Mazda',
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $this->assertEquals(
            2, count($repo->findAll())
        );
        $record = $repo->find($record->getId());
        $this->assertEquals('Mazda', $record->getName());
        $this->assertEquals(null, $record->getPlateNumber());
    }

    public function testPatchAction() {
        $originalRecord = $this->records[0];
        $this->client->request('PATCH', '/cars/'.$originalRecord->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Mazda',
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $record = $repo->find($originalRecord->getId());
        $this->assertEquals(
            2, count($repo->findAll())
        );
        $this->assertEquals('Mazda', $record->getName());
        $this->assertEquals($originalRecord->getPlateNumber(), $record->getPlateNumber());
    }

    public function testDeleteAction() {
        $record = $this->records[0];
        $id = $record->getId();
        $this->client->request('DELETE', '/cars/'.$id.'.json');
        $this->assertEquals("204", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $this->assertNull($repo->find($id));
    }
}