<?php
namespace Test\TestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Tpg\ExtjsBundle\Annotation as Extjs;

/**
 * @Extjs\Model(name="Test.document.Client")
 * @ODM\Document(collection="client")
 */
class Client {
    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     */
    protected $firstName;

    /**
     * @ODM\String
     */
    protected $lastName;

    /**
     * @ODM\ReferenceMany(targetDocument="Test\TestBundle\Document\Order", mappedBy="client")
     */
    protected $orders;
}