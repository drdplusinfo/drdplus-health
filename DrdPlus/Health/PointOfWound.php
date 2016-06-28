<?php
namespace DrdPlus\Health;

use Doctrineum\Entity\Entity;
use Granam\Integer\IntegerInterface;
use Granam\Strict\Object\StrictObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PointOfWound extends StrictObject implements Entity, IntegerInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Wound
     * @ORM\ManyToOne(targetEntity="Wound", inversedBy="pointsOfWound")
     */
    private $wound;

    public function __construct(Wound $wound)
    {
        $this->wound = $wound;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Wound
     */
    public function getWound()
    {
        return $this->wound;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return 1;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

}