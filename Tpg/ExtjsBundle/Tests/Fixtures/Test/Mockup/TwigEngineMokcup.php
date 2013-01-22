<?php
namespace Test\Mockup;

use Symfony\Bundle\TwigBundle\TwigEngine;

class TwigEngineMokcup extends TwigEngine {

    public $renderParameters;

    public function __construct() {
    }

    public function render($name, array $parameters = array()) {
        $this->renderParameters = $parameters;
        return "";
    }
}