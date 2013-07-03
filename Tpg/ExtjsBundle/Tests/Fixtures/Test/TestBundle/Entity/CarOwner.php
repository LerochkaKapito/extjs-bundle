<?php
namespace Test\TestBundle\Entity;

use Tpg\ExtjsBundle\Annotation as Extjs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use \Test\TestBundle\Entity\Car;

/**
 * @Extjs\Model
 * @Extjs\ModelProxy(
 *  name="rest",
 *  option={
 *      "url"="/mycarowners",
 *      "format"="json"
 *  },
 *  writer={
 *      "type"="json",
 *      "writeRecordId"=false,
 *      "writeAllFields"=false
 *  }
 * )
 * @ORM\Entity
 * @ORM\Table(name="car_owner")
 */
class CarOwner {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @JMS\Type("string")
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Test\TestBundle\Entity\Car", mappedBy="carOwner")
     * @JMS\Type("ArrayCollection<Test\TestBundle\Entity\Car>")
     */
    protected $cars;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cars = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return CarOwner
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add cars
     *
     * @param Car $cars
     * @return CarOwner
     */
    public function addCar(Car $cars)
    {
        $this->cars[] = $cars;
        $cars->setCarOwner($this);
        return $this;
    }

    /**
     * Remove cars
     *
     * @param Car                         $cars
     */
    public function removeCar(Car $cars)
    {
        $this->cars->removeElement($cars);
        $cars->setCarOwner(null);
    }

    /**
     * Get cars
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCars()
    {
        return $this->cars;
    }
}