<?php
namespace Tpg\ExtjsBundle\Annotation;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class ModelProxy implements Annotation {
    public $name = 'memory';
    public $option = array();
    public $reader = array();
    public $writer = array();
}