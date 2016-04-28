<?php
namespace DrdPlus\Person\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrineum\Entity\Entity;
use DrdPlus\Properties\Derived\WoundsLimit;
use Granam\Strict\Object\StrictObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="health")
 */
class Health extends StrictObject implements Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var ArrayCollection|Wound[]
     * @ORM\OneToMany(targetEntity="Wound", mappedBy="health", cascade={"all"}, orphanRemoval=true)
     */
    private $wounds;
    /**
     * @var GridOfWounds
     * @ORM\OneToOne(cascade={"all"}, fetch="EAGER", targetEntity="GridOfWounds", mappedBy="health")
     */
    private $gridOfWounds;
    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $woundsLimitValue;

    public function __construct(WoundsLimit $woundsLimit)
    {
        $this->wounds = new ArrayCollection();
        $this->woundsLimitValue = $woundsLimit->getValue();
        $this->gridOfWounds = new GridOfWounds($this);
    }

    /**
     * @param int $woundSize
     * @param WoundOrigin $woundOrigin
     * @return Wound
     * @throws \DrdPlus\Person\Health\Exceptions\WoundHasToHaveSomeValue
     */
    public function createWound($woundSize, WoundOrigin $woundOrigin)
    {
        $wound = new Wound($this, $woundSize, $woundOrigin);
        $this->getWounds()->add($wound);
        $this->getGridOfWounds()->addPointsOfWound($wound->getPointsOfWound());

        return $wound;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection|Wound[]
     */
    public function getWounds()
    {
        return $this->wounds;
    }

    /**
     * @return GridOfWounds
     */
    public function getGridOfWounds()
    {
        $this->gridOfWounds;
    }

    /**
     * @return int
     */
    public function getWoundsLimitValue()
    {
        return $this->woundsLimitValue;
    }

    /**
     * @param WoundsLimit $woundsLimit
     */
    public function changeWoundsLimit(WoundsLimit $woundsLimit)
    {
        $this->woundsLimitValue = $woundsLimit->getValue();
    }

    /**
     * @return int
     */
    public function getNumberOfSeriousInjures()
    {
        // TODO
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        return $this->getGridOfWounds()->getRemainingHealth() > 0;
    }

    /**
     * @return bool
     */
    public function isConscious()
    {
        return $this->getGridOfWounds()->getNumberOfFilledRows() >= 2;
    }

    /**
     * @return int
     */
    public function getMalusCausedByWounds()
    {
        // TODO
    }

}