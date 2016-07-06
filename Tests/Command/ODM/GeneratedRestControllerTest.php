<?php
namespace Tpg\ExtjsBundle\Tests\Command\ODM;

use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\ODM\MongoDB\LoggableCursor;
use Symfony\Component\Routing\Router;
use Test\TestBundle\Document\Order;

class GeneratedRestControllerTest extends BaseTestGeneratedRestController {

    public function testGetsAction() {
        $this->client->request('GET', '/orders.json');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(2, count($content));
    }

    public function testGetsActionWithFilterOnTotalPrice() {
        $filter = json_encode(array(
            array('property'=>'totalPrice', 'value'=>10.58)
        ));
        $this->client->request('GET', '/orders.json?filter='.$filter);
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
        $this->assertEquals('Invoice 2', $content[0]['name']);
    }

    public function testGetsActionWithSortDESC() {
        $sort = json_encode(array(
            array('property'=>'totalPrice', 'direction'=>'DESC')
        ));
        $this->client->request('GET', '/orders.json?sort='.$sort);
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invoice 2', $content[0]['name']);
        $this->assertEquals('Invoice 1', $content[1]['name']);
    }

    public function testGetsActionWithStart() {
        $this->client->request('GET', '/orders.json?start=1');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
    }

    public function testGetsActionWithLimit() {
        $this->client->request('GET', '/orders.json?limit=1');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
    }

    public function testGetsActionWithPage() {
        $this->client->request('GET', '/orders.json?page=2');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(0, count($content));
        $this->client->request('GET', '/orders.json?page=2&limit=1');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($content));
    }

    public function testGetActionWithId() {
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->getRepository('TestTestBundle:Order');
        /** @var LoggableCursor $orders */
        $orders = $repo->findAll();
        $order = reset($orders);
        $this->client->request('GET', '/orders/'.$order->getId().'.json');
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($order->getId(), $content['id']);
        $this->assertEquals($order->getName(), $content['name']);
        $this->assertEquals($order->getTotalPrice(), $content['totalPrice']);
    }

    public function testPostAction() {
        $this->client->request('POST', '/orders.json', array(), array(), array(), json_encode(array(
            'name'=>'Inveoice 3',
            'totalPrice'=>11,
        )));
        $this->assertEquals("201", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->getRepository('TestTestBundle:Order');
        $this->assertEquals(
            3, count($repo->findAll())
        );
        $record = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Inveoice 3', $record['name']);
        $this->assertEquals(11, $record['totalPrice']);
    }

    public function testPostActionWithError() {
        $this->client->request('POST', '/orders.json', array(), array(), array(), json_encode(array(
            'name'=>'BMW',
        )));
        $this->assertEquals("400", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }

    public function testPutAction() {
        $record = $this->records[0];
        $this->client->request('PUT', '/orders/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Invoice A',
            'totalPrice'=>12.11,
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->getRepository('TestTestBundle:Order');
        $this->assertEquals(
            2, count($repo->findAll())
        );
        $record = $repo->find($record->getId());
        $this->assertEquals('Invoice A', $record->getName());
        $this->assertEquals(12.11, $record->getTotalPrice());
    }

    public function testPutActionWithSomeEmpty() {
        $record = $this->records[0];
        $this->client->request('PUT', '/orders/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Invoice B',
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->getRepository('TestTestBundle:Order');
        $this->assertEquals(
            2, count($repo->findAll())
        );
        $record = $repo->find($record->getId());
        $this->assertEquals('Invoice B', $record->getName());
        $this->assertEquals(null, $record->getTotalPrice());
    }

    public function testPatchAction() {
        $originalRecord = $this->records[0];
        $this->client->request('PATCH', '/orders/'.$originalRecord->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Invoice A',
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->getRepository('TestTestBundle:Order');
        $record = $repo->find($originalRecord->getId());
        $this->assertEquals(
            2, count($repo->findAll())
        );
        $this->assertEquals('Invoice A', $record->getName());
        $this->assertEquals($originalRecord->getTotalPrice(), $record->getTotalPrice());
    }

    public function testDeleteAction() {
        $record = $this->records[0];
        $id = $record->getId();
        $this->client->request('DELETE', '/orders/'.$id.'.json');
        $this->assertEquals("204", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->getRepository('TestTestBundle:Order');
        $this->assertNull($repo->find($id));
    }
}