<?php
namespace Tpg\ExtjsBundle\Annotation\Model;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Field implements Annotation {
    public $type="string";
	public $persist=true;
}