<?php
namespace Test\TestBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Tpg\ExtjsBundle\Annotation as Extjs;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Extjs\Model(name="Test.model.Person")
 * @extjs\ModelProxy()
 */
class Person {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $firstName;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotNull
     */
    protected $lastName;
    /**
     * @ORM\Column(type="datetime")
     * @JMS\Exclude
     */
    protected $dob;
    /**
     * @ORM\Column(type="string")
     * @Assert\Email
     * @Assert\Length(min="10", max="20")
     */
    protected $email;
    /**
     * @ORM\Column(type="integer")
     */
    protected $age;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;
    /**
     * @ORM\OneToMany(targetEntity="Test\TestBundle\Model\Book", mappedBy="person")
     */
    protected $books;
    /**
     * @Assert\Regex("/^\d+\s\d$/")
     */
    protected $regex;
    /**
     * @Assert\Choice(choices={"blue","red"})
     */
    protected $color;
}