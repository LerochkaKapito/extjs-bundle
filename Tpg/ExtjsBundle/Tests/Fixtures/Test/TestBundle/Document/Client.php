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
     */
    public function addOrder(\Test\TestBundle\Document\Order $orders)
    {
        $this->orders[] = $orders;
    }

    /**
     * Remove orders
     *
     * @param Test\TestBundle\Document\Order $orders
     */
    public function removeOrder(\Test\TestBundle\Document\Order $orders)
    {
        $this->orders->removeElement($orders);
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
