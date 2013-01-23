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

    public function testEntityProperty() {
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

    public function testEntityPropertyType() {
        $this->service->generateMarkupForEntity('Test\Model\Person');
        $this->assertContains("Test.model.Person", $this->twigEngine->renderParameters['name']);
        $fieldsType = array();
        foreach ($this->twigEngine->renderParameters['fields'] as $field) {
            $fieldsType[$field['name']] = $field['type'];
        }
        $this->assertEquals("integer", $fieldsType['id']);
        $this->assertEquals("string", $fieldsType['first_name']);
    }

    public function testEntityPropertyValidation() {
        $this->service->generateMarkupForEntity('Test\Model\Person');
        $this->assertContains("Test.model.Person", $this->twigEngine->renderParameters['name']);
        $fields = array();
        foreach ($this->twigEngine->renderParameters['fields'] as $field) {
            foreach ($field['validators'] as $validator) {
                $fields[$field['name']][] = $validator['name'];
            }
        }
        $this->assertContains("notblank", $fields['first_name']);
        $this->assertContains("blank", $fields['last_name']);
        $this->assertContains("email", $fields['email']);
        $this->assertContains("minlength", $fields['email']);
        $this->assertContains("maxlength", $fields['email']);
    }
}