<?php

namespace Tpg\ExtjsBundle\Listener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\Metadata\PropertyMetadata;

class DeserializationAssociateRelationship {

    /** @var  AnnotationReader */
    protected $reader;

    public function __construct($reader) {
        $this->reader = $reader;
    }

    public function onSerializerPreDeserialize(PreDeserializeEvent $e) {
        $className = $e->getType();
        $className = $className['name'];
        $classMetadata = $e->getContext()->getMetadataFactory()->getMetadataForClass($className);
        /** @var PropertyMetadata $propertyMetadata */
        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            /** @var JoinColumn $joinColumnAnnotation */
            $joinColumnAnnotation = $this->reader->getPropertyAnnotation(
                $propertyMetadata->reflection,
                'Doctrine\ORM\Mapping\JoinColumn'
            );
            if ($joinColumnAnnotation !== null) {
                if (array_key_exists($joinColumnAnnotation->name, $e->getData())) {
                    $idValue = $e->getData();
                    $idValue = $idValue[$joinColumnAnnotation->name];
                    if ($idValue !== null) {
                        $e->setData(
                            $e->getData() + array($propertyMetadata->name => array('id' => $idValue))
                        );
                    } else {
                        $e->setData(
                            $e->getData() + array($propertyMetadata->name => null)
                        );
                    }
                }
            }
        }
    }
}