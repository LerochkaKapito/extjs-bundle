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
        /** Handle ArrayCollection or array JMS type. */
        if ($className == "ArrayCollection" || $className == "array") {
            $className = $e->getType();
            $className = $className['params'][0]['name'];
        }
        try {
        	$classMetadata = $e->getContext()->getMetadataFactory()->getMetadataForClass($className);
        } catch (\Exception $e) {
        	return;
        }
        /** @var PropertyMetadata $propertyMetadata */
        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            if ($propertyMetadata->reflection === null) continue;
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