<?php
namespace Test\TestBundle\Document;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Tpg\ExtjsBundle\Annotation as Extjs;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Test\TestBundle\Document\OrderLineItem;

/**
 * @Extjs\Model(name="Test.document.Order")
 * @ODM\Document(collection="order")
 */
class Order {
    /**
     * @ODM\Id
     * @JMS\Type("string")
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @Assert\NotNull
     */
    protected $name;

    /**
     * @ODM\EmbedMany(targetDocument="Test\TestBundle\Document\OrderLineItem")
     * @JMS\Type("ArrayCollection<Test\TestBundle\Document\OrderLineItem>")
     */
    protected $lineItems;

    /**
     * @ODM\EmbedOne(targetDocument="Test\TestBundle\Document\OrderLineItem")
     * @JMS\Type("Test\TestBundle\Document\OrderLineItem")
     */
    protected $lastLineItem;

    /**
     * @ODM\ReferenceOne(targetDocument="Test\TestBundle\Document\Client", inversedBy="orders", cascade={"persist"})
     * @JMS\Type("Test\TestBundle\Document\Client")
     */
    protected $client;

    /**
     * @ODM\Float
     * @JMS\Type("float")
     * @Assert\NotNull(groups={"post"})
     */
    protected $totalPrice;

    public function __construct()
    {
        $this->lineItems = new ArrayCollection();
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

    public function setId($id) {
        $this->id = $id;
        return $this;
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
     * @param OrderLineItem $lineItems
     *
     * @return $this
     */
    public function addLineItem(OrderLineItem $lineItems)
    {
        $this->lineItems[] = $lineItems;
        return $this;
    }

    /**
     * Remove lineItems
     *
     * @param OrderLineItem $lineItems
     */
    public function removeLineItem(OrderLineItem $lineItems)
    {
        $this->lineItems->removeElement($lineItems);
    }

    /**
     * Get lineItems
     *
     * @return ArrayCollection $lineItems
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

    /**
     * Set lastLineItem
     *
     * @param Test\TestBundle\Document\OrderLineItem $lastLineItem
     * @return self
     */
    public function setLastLineItem(\Test\TestBundle\Document\OrderLineItem $lastLineItem)
    {
        $this->lastLineItem = $lastLineItem;
        return $this;
    }

    /**
     * Get lastLineItem
     *
     * @return Test\TestBundle\Document\OrderLineItem $lastLineItem
     */
    public function getLastLineItem()
    {
        return $this->lastLineItem;
    }

    /**
     * Set client
     *
     * @param Test\TestBundle\Document\Client $client
     * @return self
     */
    public function setClient(\Test\TestBundle\Document\Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get client
     *
     * @return Test\TestBundle\Document\Client $client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function mergeLineItems(ArrayCollection $list) {
        if ($this->lineItems instanceof Collection) {
            if ($list->isEmpty()) {
                $this->lineItems = $list;
            } else {
                $this->lineItems = new ArrayCollection(array_merge(
                    $this->lineItems->toArray(),
                    $list->toArray()
                ));
            }
        } else {
            $this->lineItems = $list;
        }
    }
}
