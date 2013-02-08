<?php
namespace Tpg\ExtjsBundle\Annotation;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Direct {
    public $name;

    public function __construct($name) {
        $this->name = $name['value'];
    }
}