<?php 

namespace Tpg\ExtjsBundle\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\Context;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

use Hal\AnnotationBundle\Data\HalTypeProcessor;

class PHPCRTypeHandler implements SubscribingHandlerInterface {
	private $managerRegistry;

	/**
	 * Constructor.
	 *
	 * @param ManagerRegistry            $managerRegistry     Manager registry
	 */
	public function __construct(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry     = $managerRegistry;
	}
		
	public static function getSubscribingMethods()
	{
		return array(
			array(
				'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
				'format' => 'json',
				'type' => 'phpcr_parentdocument',
				'method' => 'deserializeParentDocument',
			),
			array(
				'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
				'format' => 'json',
				'type' => 'phpcr_parentdocument',
				'method' => 'serializeParentDocument',
			)
		);
	}
	
	public function deserializeParentDocument(VisitorInterface $visitor, $document, array $type, Context $context) {
		$manager = $this->managerRegistry->getManager();
		return $manager->find(null, $document);
	}	
	
	public function serializeParentDocument(VisitorInterface $visitor, $parentDocument, array $type, Context $context) {
		$className = get_class($parentDocument);
		$metadataFactory = $this->managerRegistry->getManagerForClass($className)->getMetadataFactory();
		$metadata = $metadataFactory->getMetadataFor($className);
		$identifierField = $metadata->getIdentifier()[0];
		if($metadata->nodename) {
			$identifierField = $metadata->nodename; 
		}
		$uuidField = $metadata->getUuidFieldName();
		if($uuidField) {
			$identifierField = $uuidField;
		}
		$reflectionField = $metadata->getReflectionProperty($identifierField);
		return $reflectionField->getValue($parentDocument);
	}
}