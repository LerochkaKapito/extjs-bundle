<?php

namespace Tpg\ExtjsBundle\Annotation;

/**
 * Mark class as extjs model
 *
 * @Annotation
 * @Target("CLASS")
 */
final class Model {
    /** @var string Extjs class name of extjs model */
    public $name;
    /** @var string Extjs class to extend from */
    public $extend = "Ext.data.Model";
    /** @var bool Generate Extjs class as base for further inheritance */
    public $generateAsBase = false;
}