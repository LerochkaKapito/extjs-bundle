<?php
namespace Test\TestBundle\Entity;

use Tpg\ExtjsBundle\Annotation as Extjs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use \Test\TestBundle\Entity\CarOwner;

/**
 * @Extjs\Model
 * @Extjs\ModelProxy(
 *  name="rest",
 *  option={
 *      "url"="/mycars",
 *      "format"="json"
 *  },
 *  writer={
 *      "type"="json",
 *      "writeRecordId"=false,
 *      "writeAllFields"=false
 *  }
 * )
 * @ORM\Entity
 * @ORM\Table(name="car")
 */
class Car {
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
     * @ORM\Column(type="string", name="plate_number")
     * @JMS\Type("string")
     */
    protected $plateNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Type("string")
     * @JMS\Groups({"post"})
     */
    protected $password;

    /**
     * @ORM\ManyToOne(targetEntity="Test\TestBundle\Entity\CarOwner", inversedBy="cars", cascade={"persist"})
     * @ORM\JoinColumn(name="car_owner_id", referencedColumnName="id")
     * @JMS\Type("Test\TestBundle\Entity\CarOwner")
     */
    protected $carOwner;

    /**
     * @param mixed $id
     *
     * @return Car
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     *
     * @return Car
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $plateNumber
     *
     * @return Car
     */
    public function setPlateNumber($plateNumber)
    {
        $this->plateNumber = $plateNumber;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlateNumber()
    {
        return $this->plateNumber;
    }

    /**
     * @param mixed $password
     *
     * @return Car
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set carOwner
     *
     * @param CarOwner $carOwner
     * @return Car
     */
    public function setCarOwner(CarOwner $carOwner = null)
    {
        $this->carOwner = $carOwner;
    
        return $this;
    }

    /**
     * Get carOwner
     *
     * @return CarOwner
     */
    public function getCarOwner()
    {
        return $this->carOwner;
    }
}