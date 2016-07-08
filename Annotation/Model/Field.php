<?php

namespace Tpg\ExtjsBundle\Annotation\Model;

/**
 * Description of field in Extjs Model
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Field
{
    /**
     * @var string Type of field
     */
    public $type = "string";
} 