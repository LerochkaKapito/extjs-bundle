<?php

namespace Tpg\ExtjsBundle\Annotation;

/**
 * Description of proxy for model
 *
 * @Annotation
 * @Target("CLASS")
 */
final class ModelProxy
{
    /** @var string Name of proxy */
    public $name = 'memory';
    /** @var array Options */
    public $option = array();
    /** @var array Reader params */
    public $reader = array();
    /** @var array Writer params */
    public $writer = array();

    /**
     * Constructor.
     *
     * @param array $values Values
     */
    public function __construct($values)
    {
        if (isset($values['value'])) {
            $this->name = "rest";
            $this->option = array(
                "url" => $values['value'],
                "format" => "json",
            );
            $this->writer = array(
                "type" => "json",
                "writeRecordId" => false,
            );
        } else {
            if (isset($values['name'])) {
                $this->name = $values['name'];
            }
        }
        if (isset($values['option'])) {
            $this->option = array_merge($this->option, $values['option']);
        }
        if (isset($values['writer'])) {
            $this->writer = array_merge($this->writer, $values['writer']);
        }
        if (isset($values['reader'])) {
            $this->reader = $values['reader'];
        }
    }
}