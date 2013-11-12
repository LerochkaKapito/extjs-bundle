<?php
/**
 * Проект billing
 * Создатель: Никита С.
 * Дата создания: 12.11.13 11:09
 */

namespace Tpg\ExtjsBundle\Annotation\Model;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Field implements Annotation {
    public $type="string";
} 