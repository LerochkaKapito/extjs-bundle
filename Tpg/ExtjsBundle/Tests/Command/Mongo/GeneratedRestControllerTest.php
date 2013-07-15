<?php
namespace Tpg\ExtjsBundle\Tests\Command\Mongo;

include_once(__DIR__.'/../../app/AppKernel.php');

use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Symfony\Component\Routing\Router;
use Test\TestBundle\Document\Order;

class GeneratedRestControllerTest extends BaseTestGeneratedRestController {

    public function testGetsAction() {
        $this->client->request('GET', '/orders.json');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($content));
    }

//    public function testGetsActionWithFilterOnName() {
//        $filter = json_encode(array(
//            array('property'=>'name','value'=>'Ford')
//        ));
//        $this->client->request('GET', '/cars.json?filter='.$filter);
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals(1, count($content));
//        $this->assertEquals('AA123', $content[0]['plateNumber']);
//    }
//
//    public function testGetsActionWithFilterOnPlateNumner() {
//        $filter = json_encode(array(
//            array('property'=>'plateNumber','value'=>'BB243')
//        ));
//        $this->client->request('GET', '/cars.json?filter='.$filter);
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals(1, count($content));
//    }
//
//    public function testGetsActionWithFilterOnBothReturnNothing() {
//        $filter = json_encode(array(
//            array('property'=>'name', 'value'=>'Ford'),
//            array('property'=>'plateNumber', 'value'=>'BB243')
//        ));
//        $this->client->request('GET', '/cars.json?filter='.$filter);
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals(0, count($content));
//    }
//
//    public function testGetsActionWithSortASC() {
//        $sort = json_encode(array(
//            array('property'=>'name', 'direction'=>'ASC')
//        ));
//        $this->client->request('GET', '/cars.json?sort='.$sort);
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals('Ford', $content[0]['name']);
//        $this->assertEquals('Honda', $content[1]['name']);
//    }
//
//    public function testGetsActionWithSortDESC() {
//        $sort = json_encode(array(
//            array('property'=>'name', 'direction'=>'DESC')
//        ));
//        $this->client->request('GET', '/cars.json?sort='.$sort);
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals('Ford', $content[1]['name']);
//        $this->assertEquals('Honda', $content[0]['name']);
//    }
//
//    public function testGetsActionWithStart() {
//        $this->client->request('GET', '/cars.json?start=1');
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals(1, count($content));
//    }
//
//    public function testGetsActionWithLimit() {
//        $this->client->request('GET', '/cars.json?limit=1');
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals(1, count($content));
//    }
//
//    public function testGetsActionWithPage() {
//        $this->client->request('GET', '/cars.json?page=2');
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals(0, count($content));
//        $this->client->request('GET', '/cars.json?page=2&limit=1');
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals(1, count($content));
//    }
//
//    public function testGetActionWithId() {
//        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
//        $cars = $repo->findAll();
//        /** @var Car $car */
//        $car = $cars[0];
//        $this->client->request('GET', '/cars/'.$car->getId().'.json');
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $content = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals($car->getId(), $content['id']);
//        $this->assertEquals($car->getName(), $content['name']);
//        $this->assertEquals($car->getPlateNumber(), $content['plateNumber']);
//    }
//
//    public function testPostAction() {
//        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
//            'name'=>'BMW',
//            'plateNumber'=>'ZZ1267',
//        )));
//        $this->assertEquals("201", $this->client->getResponse()->getStatusCode());
//        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
//        $this->assertEquals(
//            3, count($repo->findAll())
//        );
//        $record = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertEquals('BMW', $record['name']);
//        $this->assertEquals('ZZ1267', $record['plateNumber']);
//    }
//
//    public function testPostActionWithJMSGroup() {
//        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
//            'name'=>'BMW',
//            'plateNumber'=>'ZZ1267',
//            'password'=>'xxx',
//        )));
//        $this->assertEquals("201", $this->client->getResponse()->getStatusCode());
//        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
//        $record = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertArrayNotHasKey('password', $record);
//        $this->assertEquals('xxx', $repo->find($record['id'])->getPassword());
//    }
//
//    public function testPostActionWithError() {
//        $this->client->request('POST', '/cars.json', array(), array(), array(), json_encode(array(
//            'name'=>'BMW',
//        )));
//        $this->assertEquals("400", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
//    }
//
//    public function testPutAction() {
//        $record = $this->records[0];
//        $this->client->request('PUT', '/cars/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
//            'name'=>'Mazda',
//            'plateNumber'=>'AA00',
//        )));
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
//        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
//        $this->assertEquals(
//            2, count($repo->findAll())
//        );
//        $record = $repo->find($record->getId());
//        $this->assertEquals('Mazda', $record->getName());
//        $this->assertEquals('AA00', $record->getPlateNumber());
//    }
//
//    public function testPutActionWithSomeEmpty() {
//        $record = $this->records[0];
//        $this->client->request('PUT', '/cars/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
//            'name'=>'Mazda',
//        )));
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
//        $this->assertEquals(
//            2, count($repo->findAll())
//        );
//        $record = $repo->find($record->getId());
//        $this->assertEquals('Mazda', $record->getName());
//        $this->assertEquals(null, $record->getPlateNumber());
//    }
//
//    public function testPatchAction() {
//        $originalRecord = $this->records[0];
//        $this->client->request('PATCH', '/cars/'.$originalRecord->getId().'.json', array(), array(), array(), json_encode(array(
//            'name'=>'Mazda',
//        )));
//        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
//        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
//        $record = $repo->find($originalRecord->getId());
//        $this->assertEquals(
//            2, count($repo->findAll())
//        );
//        $this->assertEquals('Mazda', $record->getName());
//        $this->assertEquals($originalRecord->getPlateNumber(), $record->getPlateNumber());
//    }
//
//    public function testDeleteAction() {
//        $record = $this->records[0];
//        $id = $record->getId();
//        $this->client->request('DELETE', '/cars/'.$id.'.json');
//        $this->assertEquals("204", $this->client->getResponse()->getStatusCode());
//        $repo = $this->client->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('TestTestBundle:Car');
//        $this->assertNull($repo->find($id));
//    }
}