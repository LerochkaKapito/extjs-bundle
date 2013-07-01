<?php
namespace Tpg\ExtjsBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use Test\TestBundle\Entity\Car;
use Test\TestBundle\Entity\CarOwner;

class GeneratedRestControllerAssociationTest extends BaseTestGeneratedRestController {

    /**
     * @var CarOwner
     */
    protected $owner;

    /**
     * @var CarOwner
     */
    protected $altOwner;

    protected function setUp() {
        parent::setUp();
        /** @var EntityManager $manager */
        $manager = $this->client->getContainer()->get("doctrine.orm.default_entity_manager");
        $owner = new CarOwner();
        $owner->setName("james");
        foreach($this->records as $record) {
            $owner->addCar($record);
        }
        $manager->persist($owner);
        $altOwner = new CarOwner();
        $altOwner->setName("david");
        $manager->persist($altOwner);
        $manager->flush();
        $this->owner = $owner;
        $this->altOwner = $altOwner;
    }

    protected function tearDown() {
        parent::tearDown();
        /** @var EntityManager $manager */
        $manager = $this->createClient()->getContainer()->get("doctrine.orm.default_entity_manager");
        $manager->createQueryBuilder()->delete('Test\TestBundle\Entity\CarOwner', 'co')->getQuery()->execute();
    }

    public function testGetWithAssociation() {
        $filter = json_encode(array(
            array('property'=>'name','value'=>'Ford')
        ));
        $this->client->request('GET', '/cars.json?filter='.$filter);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('carOwner', $content[0]);
        $this->assertInternalType('array', $content[0]['carOwner']);
        $this->assertEquals($this->owner->getId(), $content[0]['carOwner']['id']);
    }

    public function testPostWithAssociation() {
        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
            'name'=>'Toyota',
            'plateNumber'=>'BBC 234',
            'carOwner' => array(
                'id' => $this->owner->getId(),
            ),
        )));
        $this->assertEquals("201", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $record = json_decode($this->client->getResponse()->getContent(), true);
        /** @var Car $car */
        $car = $repo->find($record['id']);
        $this->assertEquals($this->owner, $car->getCarOwner());
    }

    public function testPostWithNewAssociation() {
        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
            'name'=>'Toyota',
            'plateNumber'=>'BBC 234',
            'carOwner' => array(
                'name'=>'Terry'
            ),
        )));
        $this->assertEquals("201", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $record = json_decode($this->client->getResponse()->getContent(), true);
        /** @var Car $car */
        $car = $repo->find($record['id']);
        $this->assertEquals('Terry', $car->getCarOwner()->getName());
    }

    public function testPostWithNoAssociation() {
        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
            'name'=>'Toyota',
            'plateNumber'=>'BBC 234',
        )));
        $this->assertEquals("201", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $record = json_decode($this->client->getResponse()->getContent(), true);
        /** @var Car $car */
        $car = $repo->find($record['id']);
        $this->assertNull($car->getCarOwner());
    }

    public function testPutWithSameAssociation() {
        $record = $this->records[0];
        $this->client->request('PUT', '/cars/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Mazda',
            'plateNumber'=>'AA00',
            'carOwner' => array(
                'id' => $this->owner->getId()
            )
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $car = $repo->find($record->getId());
        $this->assertEquals($this->owner, $car->getCarOwner());
    }

    public function testPutWithNoAssocation() {
        $record = $this->records[0];
        $this->client->request('PUT', '/cars/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Mazda',
            'plateNumber'=>'AA00'
        )));
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $record = json_decode($this->client->getResponse()->getContent(), true);
        /** @var Car $car */
        $car = $repo->find($record['id']);
        $this->assertNull($car->getCarOwner());
    }

    public function testPutWithDifferentAssociation() {
        $record = $this->records[0];
        $this->client->request('PUT', '/cars/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Mazda',
            'plateNumber'=>'AA00',
            'carOwner' => array(
                'id' => $this->altOwner->getId()
            )
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $car = $repo->find($record->getId());
        $this->assertEquals($this->altOwner, $car->getCarOwner());
    }

    public function testPatchWithDifferentAssociation() {
        $this->client->request('PATCH', '/cars/'.$this->records[0]->getId().'.json', array(), array(), array(), json_encode(array(
            'carOwner'=>array(
                'id'=>$this->altOwner->getId()
            )
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $car = $repo->find($this->records[0]->getId());
        $this->assertEquals($this->altOwner, $car->getCarOwner());
    }

    public function testPatchWithNoAssocation() {
        $this->client->request('PATCH', '/cars/'.$this->records[0]->getId().'.json', array(), array(), array(), json_encode(array(
            'carOwner'=>null
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
        $car = $repo->find($this->records[0]->getId());
        $this->assertNull($car->getCarOwner());
    }
}