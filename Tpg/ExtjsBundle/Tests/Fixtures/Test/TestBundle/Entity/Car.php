<?php
namespace Test\TestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Tpg\ExtjsBundle\Annotation as Extjs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use \Test\TestBundle\Entity\CarOwner;

/**
 * @Extjs\Model
 * @Extjs\ModelProxy("/mycars")
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
     * @ORM\ManyToOne(targetEntity="Test\TestBundle\Entity\Car", inversedBy="relatedCars", cascade={"persist"})
     * @ORM\JoinColumn(name="related_to_id", referencedColumnName="id")
     * @JMS\Type("Test\TestBundle\Entity\Car")
     */
    protected $relatedTo;

    /**
     * @ORM\OneToMany(targetEntity="Test\TestBundle\Entity\Car", mappedBy="relatedTo", cascade={"persist"})
     * @JMS\Type("ArrayCollection<Test\TestBundle\Entity\Car>")
     */
    protected $relatedCars;

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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->relatedCars = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add relatedCars
     *
     * @param \Test\TestBundle\Entity\Car $relatedCars
     * @return Car
     */
    public function addRelatedCar(\Test\TestBundle\Entity\Car $relatedCars)
    {
        $this->relatedCars[] = $relatedCars;
    
        return $this;
    }

    /**
     * Remove relatedCars
     *
     * @param \Test\TestBundle\Entity\Car $relatedCars
     */
    public function removeRelatedCar(\Test\TestBundle\Entity\Car $relatedCars)
    {
        $this->relatedCars->removeElement($relatedCars);
    }

    /**
     * Get relatedCars
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedCars()
    {
        return $this->relatedCars;
    }

    /**
     * Set relatedTo
     *
     * @param \Test\TestBundle\Entity\Car $relatedTo
     * @return Car
     */
    public function setRelatedTo(\Test\TestBundle\Entity\Car $relatedTo = null)
    {
        $this->relatedTo = $relatedTo;
    
        return $this;
    }

    /**
     * Get relatedTo
     *
     * @return \Test\TestBundle\Entity\Car 
     */
    public function getRelatedTo()
    {
        return $this->relatedTo;
    }

    public function mergeRelatedCars(ArrayCollection $list) {
        $this->relatedCars = new ArrayCollection(array_merge($this->relatedCars->toArray(), $list->toArray()));
    }
}