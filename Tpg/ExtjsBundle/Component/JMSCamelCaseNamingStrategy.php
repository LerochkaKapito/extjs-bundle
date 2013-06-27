<?php

namespace Tpg\ExtjsBundle\Component;

use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

class JMSCamelCaseNamingStrategy implements PropertyNamingStrategyInterface {

    /**
     * Translates the name of the property to the serialized version.
     *
     * @param PropertyMetadata $property
     *
     * @return string
     */
    public function translateName(PropertyMetadata $property)
    {
        return $property->name;
    }
}