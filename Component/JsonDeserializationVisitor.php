<?php
namespace Tpg\ExtjsBundle\Component;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor as Base;
use JMS\Serializer\Metadata\PropertyMetadata;
use Symfony\Component\Yaml\Exception\RuntimeException;

/**
 * Class JsonDeserializationVisitor
 * This extend from JMS\Serializer\JsonDeserializationVisitor. It's function is to be able to merge One To Many related
 * property in an entity. related_action need to be set to "merge" on the context before this will be activated.
 *
 * @package Tpg\ExtjsBundle\Component
 */
class JsonDeserializationVisitor extends Base {
    public function visitProperty(PropertyMetadata $metadata, $data, Context $context) {
        $name = $this->namingStrategy->translateName($metadata);

        if (null === $data || ! is_array($data) || ! array_key_exists($name, $data)) {
            return;
        }

        if ( ! $metadata->type) {
            throw new RuntimeException(sprintf('You must define a type for %s::$%s.', $metadata->reflection->class, $metadata->name));
        }
        $v = $data[$name] !== null ? $this->getNavigator()->accept($data[$name], $metadata->type, $context) : null;

        if (null === $metadata->setter) {
            if ($context->attributes->get("related_action")->isDefined() &&
                $context->attributes->get("related_action")->get() == "merge" &&
                $metadata->type['name'] == "ArrayCollection" &&
                $metadata->reflection->getValue($this->getCurrentObject()) !== null &&
                $metadata->reflection->getValue($this->getCurrentObject())->count() > 0
            ) {
                $metadata->reflection->setValue($this->getCurrentObject(), new ArrayCollection(
                    array_merge(
                        $metadata->reflection->getValue($this->getCurrentObject())->toArray(),
                        $v->toArray()
                    )
                ));
            } else {
                $metadata->reflection->setValue($this->getCurrentObject(), $v);
            }

            return;
        }

        $this->getCurrentObject()->{$metadata->setter}($v);
    }
}