<?php
namespace Tpg\ExtjsBundle\Tests\Command\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader;

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
        $kernel->boot();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($em);
        $classNames = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $classMetaData = [];
        foreach ($classNames as $className) {
            $classMetaData[] = $em->getClassMetadata($className);
        }
        $tool->dropSchema($classMetaData);
        $tool->createSchema($classMetaData);

        $kernel->shutdown();
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
    }
}