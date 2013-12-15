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

    public function __construct($values) {
        if (isset($values['value'])) {
            $this->name = "rest";
            $this->option = array(
                "url"=>$values['value'],
                "format"=>"json",
            );
            $this->writer = array(
                "type"=>"json",
                "writeRecordId"=>false,
            );
        } else {
            if (isset($values['name'])) $this->name = $values['name'];
        }
        if (isset($values['option'])) $this->option = array_merge($this->option, $values['option']);
        if (isset($values['writer'])) $this->writer = array_merge($this->writer, $values['writer']);
        if (isset($values['reader'])) $this->reader = $values['reader'];
    }
}