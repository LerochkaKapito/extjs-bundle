<?php
namespace Test\TestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Tpg\ExtjsBundle\Annotation as Extjs;

/**
 * @Extjs\Model(name="Test.document.Client")
 * @MongoDB\Document(collection="client")
 */
class Client {
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $firstName;

    /**
     * @MongoDB\String
     */
    protected $lastName;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Test\TestBundle\Document\Order", mappedBy="client")
     */
    protected $orders;
}