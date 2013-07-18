<?php

namespace Tpg\ExtjsBundle\Tests\Command\ODM;

use Test\TestBundle\Document\Order;

class GeneratedRestControllerReferenceAssociationTest extends BaseTestGeneratedRestController {
    public function testGetWithAssociation() {
        $filter = json_encode(array(
            array('property'=>'name','value'=>'Invoice 1')
        ));
        $this->client->request('GET', '/orders.json?filter='.$filter);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('client', $content[0]);
        $this->assertInternalType('array', $content[0]['client']);
        $this->assertEquals($this->records['0']->getClient()->getId(), $content[0]['client']['id']);
    }

    public function testPostWithNewAssociation() {
        $this->client->request('POST', '/orders.json', array(), array(), array(), json_encode(array(
            'name'=>'Invoice New',
            'totalPrice'=>1.99,
            'client' => array(
                'firstName' => 'James',
                'lastName' => 'Bond',
            ),
        )));
        $this->assertEquals("201", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        $record = json_decode($this->client->getResponse()->getContent(), true);
        /** @var Order $order */
        $order = $repo->find($record['id']);
        $this->assertEquals('Invoice New', $order->getName());
        $this->assertNotNull($order->getClient());
        $this->assertNotNull($order->getClient()->getId());
        $this->assertEquals('James', $order->getClient()->getFirstName());
    }

    public function testPutWithDifferentAssociation() {
        $record = $this->records[0];
        $originalClient = $record->getClient();
        $this->client->request('PUT', '/orders/'.$record->getId().'.json', array(), array(), array(), json_encode(array(
            'name'=>'Invoice 1',
            'client' => array(
                'id' => $this->clientDocument->getId()
            ),
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        /** @var Order $order */
        $order = $repo->find($record->getId());
        $this->assertEquals($this->clientDocument->getId(), $order->getClient()->getId());

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
        $this->assertNull($order->getClient());
    }

    public function testPatchWithDifferentAssociation() {
        $this->client->request('PATCH', '/orders/'.$this->records[0]->getId().'.json', array(), array(), array(), json_encode(array(
            'client' => array(
                'id' => $this->clientDocument->getId()
            ),
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        /** @var Order $order */
        $order = $repo->find($this->records[0]->getId());
        $this->assertEquals($this->records[0]->getLineItems(), $order->getLineItems());
        $this->assertEquals($this->clientDocument->getId(), $order->getClient()->getId());
    }

    public function testPatchWithNoAssocation() {
        $this->client->request('PATCH', '/orders/'.$this->records[0]->getId().'.json', array(), array(), array(), json_encode(array(
            'client'=>null
        )));
        $this->assertEquals("200", $this->client->getResponse()->getStatusCode());
        $repo = $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager')->getRepository('TestTestBundle:Order');
        /** @var Order $order */
        $order = $repo->find($this->records[0]->getId());
        $this->assertEquals($this->records[0]->getLineItems(), $order->getLineItems());
        $this->assertNull($order->getClient());
    }
}