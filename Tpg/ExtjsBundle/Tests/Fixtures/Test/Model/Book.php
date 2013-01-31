<?php
namespace Test\Model;

use Doctrine\ORM\Mapping as ORM;
use Tpg\ExtjsBundle\Annotation as Extjs;

/**
 * @Extjs\Model(
 *     name="Test.model.Book",
 *     generateAsBase=true
 * )
 */
class Book {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Test\Model\Person", inversedBy="books")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;
}