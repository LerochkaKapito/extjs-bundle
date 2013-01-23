<?php
namespace Test\Model;

use Doctrine\ORM\Mapping as ORM;
use Tpg\ExtjsBundle\Annotation as Extjs;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Extjs\Model(name="Test.model.Person")
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
     * @Assert\Blank
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
     * @Assert\MinLength(limit = 10)
     * @Assert\MaxLength(limit = 20)
     */
    protected $email;
    /**
     * @ORM\OneToMany(targetEntity="Test\Model\Book", mappedBy="person")
     */
    protected $books;
}