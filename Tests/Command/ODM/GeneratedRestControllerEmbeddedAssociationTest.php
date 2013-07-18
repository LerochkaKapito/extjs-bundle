<?php
namespace Tpg\ExtjsBundle\Tests\Command\ODM;

use Test\TestBundle\Document\Order;

class GeneratedRestControllerEmbeddedAssociationTest extends BaseTestGeneratedRestController {

    public function testGetWithAssociation() {
        $filter = json_encode(array(
            array('property'=>'name','value'=>'Invoice 1')
        ));
        $this->client->request('GET', '/orders.json?filter='.$filter);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('lineItems', $content[0]);
        $this->assertInternalType('array', $content[0]['lineItems']);
        $this->assertEquals(1, count($content[0]['lineItems']));
    }

    public function testPostWithNewAssociation() {
        $this->client->request('POST', '/orders.json', array(), array(), array(), json_encode(array(
            'name'=>'Invoice New',
            'totalPrice'=>1.99,
            'lineItems' => array(
                array(
                    'productId' => 12,
                    'quantity' => 1,
                    'price' => 1.99,
                    'totalPrice' => 1.99
                )
            ),
        )));
        $this->assertEquals("201", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        $record = json_decode($this->client->getResponse()->getContent(), true);
        /** @var Order $order */
        $order = $repo->find($record['id']);
        $this->assertEquals('Invoice New', $order->getName());
        $this->assertEquals(1, $order->getLineItems()->count());
        $this->assertEquals(12, $order->getLineItems()->first()->getProductId());
    }

    public function testPutWithDifferentAssociation() {
        $record = $this->records[0];
        $this->client->request('PUT', '/orders/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Invoice 1',
            'lineItems' => array(
                array(
                    'productId' => 12,
                    'quantity' => 1,
                    'price' => 1.99,
                    'totalPrice' => 1.99
                )
            )
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        /** @var Order $order */
        $order = $repo->find($record->getId());
        $this->assertNull($order->getTotalPrice());
        $this->assertEquals(1, $order->getLineItems()->count());
        $this->assertEquals(12, $order->getLineItems()->first()->getProductId());
    }

    public function testPutWithNoAssocation() {
        $record = $this->records[0];
        $this->client->request('PUT', '/orders/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Invoice 1'
        )));
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        $record = json_decode($this->client->getResponse()->getContent(), true);
        /** @var Order $order */
        $order = $repo->find($record['id']);
        $this->assertEquals(0, $order->getLineItems()->count());
    }

    public function testPatchWithDifferentAssociation() {
        $this->client->request('PATCH', '/orders/'.$this->records[0]->getId().'.json', array(), array(), array(), json_encode(array(
            'lineItems' => array(
                array(
                    'productId' => 12,
                    'quantity' => 1,
                    'price' => 1.99,
                    'totalPrice' => 1.99
                )
            ),
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        /** @var Order $order */
        $order = $repo->find($this->records[0]->getId());
        $this->assertEquals(2, $order->getLineItems()->count());
    }

    public function testPatchWithNoAssocation() {
        $this->client->request('PATCH', '/orders/'.$this->records[0]->getId().'.json', array(), array(), array(), json_encode(array(
            'lineItems'=>array()
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        /** @var Order $order */
        $order = $repo->find($this->records[0]->getId());
        $this->assertEquals(1, $order->getLineItems()->count());
    }
}