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

    public function testEntityProperty() {
        $this->service->generateMarkupForEntity('Test\TestBundle\Document\Order');
        $this->assertContains("Test.document.Order", $this->twigEngine->renderParameters['name']);
        $fieldsName = array();
        foreach ($this->twigEngine->renderParameters['fields'] as $field) {
            $fieldsName[] = $field['name'];
        }
        $this->assertContains("id", $fieldsName);
        $this->assertContains("name", $fieldsName);
        $this->assertContains("lineItems", $fieldsName);
        $this->assertContains("totalPrice", $fieldsName);
    }

    public function testEntityPropertyType() {
        $this->service->generateMarkupForEntity('Test\TestBundle\Document\Order');
        $fieldsType = array();
        foreach ($this->twigEngine->renderParameters['fields'] as $field) {
            $fieldsType[$field['name']] = $field['type'];
        }
        $this->assertEquals("string", $fieldsType['id']);
        $this->assertEquals("string", $fieldsType['name']);
        $this->assertContains("float", $fieldsType['totalPrice']);
    }
}