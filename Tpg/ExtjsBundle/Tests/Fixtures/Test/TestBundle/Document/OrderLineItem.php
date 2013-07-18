<?php
namespace Test\TestBundle\Document;

use Tpg\ExtjsBundle\Annotation as Extjs;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @Extjs\Model(name="Test.document.OrderLineItem")
 * @ODM\EmbeddedDocument
 */
class OrderLineItem {
    /**
     * @ODM\Int
     * @JMS\Type("integer")
     */
    protected $productId;

    /**
     * @ODM\Int
     * @JMS\Type("integer")
     */
    protected $quantity;

    /**
     * @ODM\Float
     * @JMS\Type("double")
     */
    protected $price;

    /**
     * @ODM\Float
     * @JMS\Type("double")
     */
    protected $total;

    /**
     * Set productId
     *
     * @param int $productId
     * @return self
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * Get productId
     *
     * @return int $productId
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set quantity
     *
     * @param int $quantity
     * @return self
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Get quantity
     *
     * @return int $quantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return float $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set total
     *
     * @param float $total
     * @return self
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return float $total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Static class to create a new instance of OrderLineItem
     * @return OrderLineItem
     */
    public static function newInstance() {
        return new OrderLineItem();
    }
}
