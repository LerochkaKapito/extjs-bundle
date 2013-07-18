<?php
namespace Test\TestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Tpg\ExtjsBundle\Annotation as Extjs;
use JMS\Serializer\Annotation as JMS;

/**
 * @Extjs\Model(name="Test.document.Client")
 * @ODM\Document(collection="client")
 */
class Client {
    /**
     * @ODM\Id
     * @JMS\Type("string")
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     */
    protected $firstName;

    /**
     * @ODM\String
     * @JMS\Type("string")
     */
    protected $lastName;

    /**
     * @ODM\ReferenceMany(targetDocument="Test\TestBundle\Document\Order", mappedBy="client")
     */
    protected $orders;

    public function __construct()
    {
        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Add orders
     *
     * @param Test\TestBundle\Document\Order $orders
     *
     * @return $this
     */
    public function addOrder(\Test\TestBundle\Document\Order $orders)
    {
        $this->orders[] = $orders;
        $orders->setClient($this);
        return $this;
    }

    /**
     * Remove orders
     *
     * @param Test\TestBundle\Document\Order $orders
     */
    public function removeOrder(\Test\TestBundle\Document\Order $orders)
    {
        $this->orders->removeElement($orders);
        $orders->setClient(null);
    }

    /**
     * Get orders
     *
     * @return Doctrine\Common\Collections\Collection $orders
     */
    public function getOrders()
    {
        return $this->orders;
    }
}
