<?php
namespace Test\TestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Tpg\ExtjsBundle\Annotation as Extjs;

/**
 * @Extjs\Model(name="Test.document.Order")
 * @ODM\Document(collection="order")
 */
class Order {
    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     */
    protected $name;

    /**
     * @ODM\EmbedMany(targetDocument="Test\TestBundle\Document\OrderLineItem")
     */
    protected $lineItems;

    /**
     * @ODM\EmbedOne(targetDocument="Test\TestBundle\Document\OrderLineItem")
     */
    protected $lastLineItem;

    /**
     * @ODM\ReferenceOne(targetDocument="Test\TestBundle\Document\Client", inversedBy="orders")
     */
    protected $client;

    /**
     * @ODM\Float
     */
    protected $totalPrice;

    public function __construct()
    {
        $this->lineItems = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add lineItems
     *
     * @param Test\TestBundle\Document\OrderLineItem $lineItems
     */
    public function addLineItem(\Test\TestBundle\Document\OrderLineItem $lineItems)
    {
        $this->lineItems[] = $lineItems;
        return $this;
    }

    /**
     * Remove lineItems
     *
     * @param Test\TestBundle\Document\OrderLineItem $lineItems
     */
    public function removeLineItem(\Test\TestBundle\Document\OrderLineItem $lineItems)
    {
        $this->lineItems->removeElement($lineItems);
    }

    /**
     * Get lineItems
     *
     * @return Doctrine\Common\Collections\Collection $lineItems
     */
    public function getLineItems()
    {
        return $this->lineItems;
    }

    /**
     * Set totalPrice
     *
     * @param float $totalPrice
     * @return self
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return float $totalPrice
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }
}
