<?php
namespace Tpg\ExtjsBundle\Tests\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Form\Util\PropertyPath;
use Test\Mockup\TwigEngineMokcup;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Tpg\ExtjsBundle\Service\GeneratorService;

class GeneratorServiceTest extends TestCase {

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

    public function testGeneratingEntity() {
        $this->service->generateMarkupForEntity('Test\Model\Person');
        $this->assertContains("Test.model.Person", $this->twigEngine->renderParameters['name']);
        $fieldsName = array();
        foreach ($this->twigEngine->renderParameters['fields'] as $field) {
            $fieldsName[] = $field['name'];
        }
        $this->assertContains("id", $fieldsName);
        $this->assertContains("first_name", $fieldsName);
        $this->assertContains("last_name", $fieldsName);
        $this->assertNotContains("age", $fieldsName);
    }
}