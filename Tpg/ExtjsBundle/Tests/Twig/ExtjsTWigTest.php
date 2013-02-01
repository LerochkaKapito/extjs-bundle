<?php
namespace Tpg\ExtjsBundle\Tests\Twig;

use Symfony\Component\DependencyInjection\Container;
use Tpg\ExtjsBundle\Twig\ExtjsExtension;

class ExtjsTWigTest extends \Twig_Test_IntegrationTestCase {

    protected function getExtensions()
    {
        $generator = $this->getMock(
            'Tpg\ExtjsBundle\Service\GeneratorService',
            array('generateMarkupForEntity')
        );
        $generator->expects($this->any())
            ->method('generateMarkupForEntity')
            ->will($this->returnCallback(function($entity) {
                return $entity;
            }));
        $router = $this->getMock(
            'Symfony\Bundle\FrameworkBundle\Routing\Router',
            array('generate'),
            array(new Container(), null)
        );
        $router->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(function($url, $parameters) {
                $this->assertEquals('extjs_generate_model', $url);
                $this->assertEquals(count($parameters['model']), 2);
                $url = '/generateModel.js?';
                foreach ($parameters['model'] as $model) {
                    $url .= 'model[]='.$model.'&';
                }
                return substr($url, 0, -1);
            }));
        $ext = new ExtjsExtension();
        $ext->setGenerator($generator);
        $ext->setRouter($router);
        return array(
            $ext
        );
    }

    protected function getFixturesDir()
    {
        return __DIR__.'/tests/';
    }
}