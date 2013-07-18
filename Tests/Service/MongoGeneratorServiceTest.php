<?php
namespace Tpg\ExtjsBundle\Tests\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Test\TestBundle\Mockup\TwigEngineMokcup;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Tpg\ExtjsBundle\Service\GeneratorService;

class MongoGeneratorServiceTest extends TestCase {
    /** @var GeneratorService */
    protected $service;

    /** @var TwigEngineMokcup */
    protected $twigEngine;

    protected function setUp() {
        parent::setUp();
        $this->service = new GeneratorService();
        $this->service->setAnnotationReader(new AnnotationReader());
        $this->twigEngine = new TwigEngineMokcup();
        $this->service->setTwigEngine($this->twigEngine);
    }

    public function testDocumentProperty() {
        $this->service->generateMarkupForEntity('Test\TestBundle\Document\Order');
        $this->assertContains("Test.document.Order", $this->twigEngine->renderParameters['name']);
        $fieldsName = array();
        foreach ($this->twigEngine->renderParameters['fields'] as $field) {
            $fieldsName[] = $field['name'];
        }
        $this->assertContains("id", $fieldsName);
        $this->assertContains("name", $fieldsName);
        $this->assertContains("totalPrice", $fieldsName);
    }

    public function testDocumentPropertyType() {
        $this->service->generateMarkupForEntity('Test\TestBundle\Document\Order');
        $fieldsType = array();
        foreach ($this->twigEngine->renderParameters['fields'] as $field) {
            $fieldsType[$field['name']] = $field['type'];
        }
        $this->assertEquals("string", $fieldsType['id']);
        $this->assertEquals("string", $fieldsType['name']);
        $this->assertContains("float", $fieldsType['totalPrice']);
    }

    public function testEmbeddedDocument() {
        $this->service->generateMarkupForEntity('Test\TestBundle\Document\Order');
        $associations = array();
        foreach ($this->twigEngine->renderParameters['associations'] as $assoc) {
            $associations[$assoc['name']] = $assoc;
        }
        $this->assertEquals('Test.document.OrderLineItem', $associations['lineItems']['model']);
        $this->assertEquals('lineItems', $associations['lineItems']['name']);
        $this->assertEquals('EmbedMany', $associations['lineItems']['type']);
        $this->assertEquals('Test.document.OrderLineItem', $associations['lastLineItem']['model']);
        $this->assertEquals('lastLineItem', $associations['lastLineItem']['name']);
        $this->assertEquals('EmbedOne', $associations['lastLineItem']['type']);
    }

    public function testReferenceDocument() {
        $this->service->generateMarkupForEntity('Test\TestBundle\Document\Client');
        $associations = array();
        foreach ($this->twigEngine->renderParameters['associations'] as $assoc) {
            $associations[$assoc['name']] = $assoc;
        }
        $this->assertEquals('Test.document.Order', $associations['orders']['model']);
        $this->assertEquals('orders', $associations['orders']['name']);
        $this->assertEquals('ReferenceMany', $associations['orders']['type']);

        $this->service->generateMarkupForEntity('Test\TestBundle\Document\Order');
        $associations = array();
        foreach ($this->twigEngine->renderParameters['associations'] as $assoc) {
            $associations[$assoc['name']] = $assoc;
        }
        $this->assertEquals('Test.document.Client', $associations['client']['model']);
        $this->assertEquals('client', $associations['client']['name']);
        $this->assertEquals('ReferenceOne', $associations['client']['type']);

    }
}