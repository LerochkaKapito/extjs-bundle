<?php
namespace Tpg\ExtjsBundle\Component;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\VisitorInterface;

class FailedObjectConstructor implements ObjectConstructorInterface {
    /**
     * Constructs a new object.
     *
     * Implementations could for example create a new object calling "new", use
     * "unserialize" techniques, reflection, or other means.
     *
     * @param VisitorInterface $visitor
     * @param ClassMetadata    $metadata
     * @param mixed            $data
     * @param array            $type ["name" => string, "params" => array]
     *
     * @return object
     */
    public function construct(VisitorInterface $visitor, ClassMetadata $metadata, $data, array $type)
    {
        throw new \Exception('Fail to construct the object');
    }
}