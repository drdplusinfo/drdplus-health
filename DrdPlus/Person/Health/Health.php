<?php
namespace DrdPlus\Person\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrineum\Entity\Entity;
use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Properties\Derived\WoundsLimit;
use DrdPlus\RollsOn\Traps\RollOnWillAgainstMalus;
use DrdPlus\RollsOn\Traps\RollOnWill;
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
     * @var ArrayCollection|AfflictionByWound[]
     * @ORM\OneToMany(targetEntity="AfflictionByWound", mappedBy="health", cascade={"all"}, orphanRemoval=true)
     */
    private $afflictions;
    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $woundsLimitValue;
    /**
     * Separates new and old (or serious) injuries.
     * @var TreatmentBoundary
     * @ORM\Column(type="treatment_boundary")
     */
    private $treatmentBoundary;
    /**
     * @var GridOfWounds|null is just a helper, does not need to be persisted
     */
    private $gridOfWounds;

    public function __construct(WoundsLimit $woundsLimit)
    {
        $this->wounds = new ArrayCollection();
        $this->woundsLimitValue = $woundsLimit->getValue();
        $this->afflictions = new ArrayCollection();
        $this->treatmentBoundary = TreatmentBoundary::getIt(0);
    }

    /**
     * @param WoundSize $woundSize
     * @param SpecificWoundOrigin $seriousWoundOrigin Beware, if the wound size is considered as serious, OrdinaryWoundOrigin will be used instead
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return Wound
     */
    public function createWound(WoundSize $woundSize, SpecificWoundOrigin $seriousWoundOrigin, Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        $wound = new Wound($this, $woundSize, $this->isSeriousInjury($woundSize) ? $seriousWoundOrigin : OrdinaryWoundOrigin::getIt());
        $this->wounds->add($wound);
        if ($this->maySufferFromPain()) {
            $this->rollAgainstMalusFromWoundsOnWound($will, $roller2d6DrdPlus);
        }
        if ($wound->isSerious()) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->treatmentBoundary = TreatmentBoundary::getIt($this->getTreatmentBoundary()->getValue() + $wound->getValue());
        }

        return $wound;
    }

    private function rollAgainstMalusFromWoundsOnWound(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        $newRoll = $this->createRollAgainstMalusFromWounds($will, $roller2d6DrdPlus);
        // greater (or same of course) malus remains; can not be lowered on new wounds
        if ($this->rollAgainstMalusFromWounds !== null
            && $this->rollAgainstMalusFromWounds->getMalusValue() >= $newRoll->getMalusValue()
        ) {
            return;
        }
        $this->rollAgainstMalusFromWounds = $newRoll;
    }

    private function isSeriousInjury(WoundSize $woundSize)
    {
        return $this->getGridOfWounds()->calculateFilledHalfRowsFor($woundSize->getValue()) > 0;
    }

    private function maySufferFromPain()
    {
        // if person became unconscious than the roll against pain malus is not re-rolled
        return $this->getGridOfWounds()->getNumberOfFilledRows() >= GridOfWounds::PAIN_NUMBER_OF_ROWS && $this->isConscious();
    }

    /**
     * Serious wound MAY be of lower value than half of wound row (but ordinary wound HAS TO be lower than half of row).
     * Wound origin MUST NOT be ordinary wound origin.
     * @param AfflictionByWound $afflictionByWound
     * @throws \DrdPlus\Person\Health\Exceptions\UnknownAfflictionOriginatingWound
     * @throws \DrdPlus\Person\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    public function addAffliction(AfflictionByWound $afflictionByWound)
    {
        if (!$this->doesHaveThatWound($afflictionByWound->getWound())) {
            throw new Exceptions\UnknownAfflictionOriginatingWound(
                "Given affliction to add {$afflictionByWound->getName()} comes from unknown wound"
                . " of value {$afflictionByWound->getWound()} and origin {$afflictionByWound->getWound()->getWoundOrigin()}"
                . 'Have you created that wound by current health?'
            );
        }
        if ($this->doesHaveThatAffliction($afflictionByWound)) {
            throw new Exceptions\AfflictionIsAlreadyRegistered(
                "Given instance of affliction with name {$afflictionByWound->getName()} is already added"
            );
        }
        $this->afflictions->add($afflictionByWound);
    }

    private function doesHaveThatWound(Wound $givenWound)
    {
        foreach ($this->wounds as $registeredWound) {
            if ($givenWound === $registeredWound) {
                return true;
            }
        }

        return false;
    }

    private function doesHaveThatAffliction(AfflictionByWound $givenAffliction)
    {
        foreach ($this->afflictions as $registeredAffliction) {
            if ($givenAffliction === $registeredAffliction) {
                return true;
            }
        }

        return false;
    }

    /**
     * Also sets treatment boundary to unhealed wounds after. Even if the heal itself heals nothing!
     * @param HealingPower $healingPower
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return int amount of actually healed points of wounds
     */
    public function healNewOrdinaryWoundsUpTo(HealingPower $healingPower, Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        // can heal new and ordinary wounds only, up to limit by current treatment boundary
        $healed = 0;
        // can heal only ordinary wounds and also only new ones (delimited by treatment boundary)
        $healUpTo = min($healingPower->getValue(), $this->getNewOrdinaryWoundsSum() - $this->treatmentBoundary->getValue());
        foreach ($this->getNewOrdinaryWounds() as $newOrdinaryWound) {
            if ($healUpTo < $healed) { // we do not spent all the healing power
                $healed += $newOrdinaryWound->heal($healUpTo - $healed);
            }
            $newOrdinaryWound->setOld(); // every ordinary wound become "old"
        }
        // all unhealed wounds become "old" (and can be healed only by a professional or nature itself)
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getUnhealedWoundsSum());
        if ($healed > 0) { // otherwise wounds remain the same, pain remains the same
            if ($this->maySufferFromPain()) {
                $this->reRollAgainstMalusFromWoundsOnHeal($will, $roller2d6DrdPlus);
            } else if ($this->isConscious()) {
                $this->rollAgainstMalusFromWounds = null; // pain is gone and person feel it - lets remove the roll and malus
            }
        }

        return $healed;
    }

    /**
     * @return Wound[]|Collection
     */
    private function getNewOrdinaryWounds()
    {
        return $this->getUnhealedWounds()->filter(
            function (Wound $wound) {
                return !$wound->isSerious() && !$wound->isOld();
            }
        );
    }

    /**
     * @param Wound $seriousWound
     * @param HealingPower $healingPower
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return int amount of healed points of wounds
     */
    public function healSeriousWound(Wound $seriousWound, HealingPower $healingPower, Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if (!$this->doesHaveThatWound($seriousWound)) {
            throw new \LogicException;
        }
        if (!$seriousWound->isSerious()) {
            throw new \LogicException;
        }
        if ($seriousWound->isOld()) {
            throw new \LogicException;
        }
        $healed = $seriousWound->heal($healingPower->getValue());
        $seriousWound->setOld();
        // treatment boundary is taken with wounds down together
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->treatmentBoundary->getValue() - $healed);
        if ($this->maySufferFromPain()) {
            $this->reRollAgainstMalusFromWoundsOnHeal($will, $roller2d6DrdPlus);
        } else if ($this->isConscious()) {
            $this->rollAgainstMalusFromWounds = null; // pain is gone and person feel it - lets remove the roll and malus
        }

        return $healed;
    }

    /**
     * Regenerate any wound, both ordinary and serious, both new and old, by natural or unnatural way.
     * @param int $regenerateUpTo
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return int actually regenerated amount
     */
    public function regenerate($regenerateUpTo, Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        // every wound becomes old after this
        $regenerated = 0;
        foreach ($this->getUnhealedWounds() as $unhealedWound) {
            if ($regenerateUpTo < $regenerated) { // we do not spent all the healing power
                $regenerated += $unhealedWound->heal($regenerateUpTo - $regenerated);
            }
            $unhealedWound->setOld(); // every wound become "old"
        }
        // all unhealed wounds become "old" (and can be healed only by a professional or nature itself)
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getUnhealedWoundsSum());
        if ($regenerated > 0) { // otherwise wounds remain the same, pain remains the same
            if ($this->maySufferFromPain()) {
                $this->reRollAgainstMalusFromWoundsOnHeal($will, $roller2d6DrdPlus);
            } else if ($this->isConscious()) {
                $this->rollAgainstMalusFromWounds = null; // pain is gone and person feel it - lets remove the roll and malus
            }
        }

        return $regenerated;
    }

    /**
     * Usable for info about amount of wounds which can be healed by basic healing
     * @return int
     */
    public function getNewOrdinaryWoundsSum()
    {
        return array_sum(
            array_map(
                function (Wound $wound) {
                    return $wound->getValue();
                },
                $this->getNewOrdinaryWounds()->toArray()
            )
        );
    }

    /**
     * @return int
     */
    public function getUnhealedSeriousWoundsSum()
    {
        return array_sum(
            array_map(
                function (Wound $wound) {
                    return $wound->getValue();
                },
                $this->getUnhealedSeriousWounds()->toArray()
            )
        );
    }

    /**
     * @return Wound[]|Collection
     */
    private function getUnhealedSeriousWounds()
    {
        return $this->getUnhealedWounds()->filter(
            function (Wound $wound) {
                return $wound->isSerious();
            }
        );
    }

    /**
     * @return int
     */
    public function getUnhealedWoundsSum()
    {
        return $this->getGridOfWounds()->getSumOfWounds();
    }

    /**
     * @return int
     */
    public function getHealthMaximum()
    {
        return $this->getWoundsLimitValue() * GridOfWounds::TOTAL_NUMBER_OF_ROWS;
    }

    /**
     * @return int
     */
    public function getRemainingHealthAmount()
    {
        return max(0, $this->getHealthMaximum() - $this->getUnhealedWoundsSum());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|Wound[]
     */
    public function getUnhealedWounds()
    {
        // results into different instance of Collection which avoids external change of the original
        return $this->wounds->filter(
            function (Wound $wound) {
                return !$wound->isHealed();
            }
        );
    }

    /**
     * @return GridOfWounds
     */
    public function getGridOfWounds()
    {
        if ($this->gridOfWounds === null) {
            $this->gridOfWounds = new GridOfWounds($this);
        }

        return $this->gridOfWounds;
    }

    /**
     * Looking for a setter? Sorry but affliction can be caused only by a new wound.
     * @return Collection|AfflictionByWound[]
     */
    public function getAfflictions()
    {
        return clone $this->afflictions; // cloned to avoid external change of the collection
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
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     */
    public function changeWoundsLimit(WoundsLimit $woundsLimit, Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->getWoundsLimitValue() === $woundsLimit->getValue()) {
            return;
        }
        $previousHealthMaximum = $this->getHealthMaximum();
        $this->woundsLimitValue = $woundsLimit->getValue();
        if ($previousHealthMaximum > $this->getHealthMaximum()) { // current wounds relatively increases
            $this->reRollAgainstMalusFromWoundsOnWound($will, $roller2d6DrdPlus);
        } elseif ($previousHealthMaximum < $this->getHealthMaximum()) { // current wounds relatively decreases
            $this->reRollAgainstMalusFromWoundsOnHeal($will, $roller2d6DrdPlus);
        }
    }

    private function reRollAgainstMalusFromWoundsOnWound(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->rollAgainstMalusFromWounds === null) {
            return;
        }
        $this->rollAgainstMalusFromWoundsOnWound($will, $roller2d6DrdPlus);
    }

    private function reRollAgainstMalusFromWoundsOnHeal(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->rollAgainstMalusFromWounds === null) {
            return;
        }
        $newRoll = $this->createRollAgainstMalusFromWounds($will, $roller2d6DrdPlus);
        // lower (or same of course) malus remains; can not be increased on healing
        if ($this->rollAgainstMalusFromWounds->getMalusValue() <= $newRoll->getMalusValue()) {
            return;
        }
        $this->rollAgainstMalusFromWounds = $newRoll;
    }

    /**
     * Treatment boundary is set automatically on any heal (lowering wounds) or new serious injury
     * @return TreatmentBoundary
     */
    public function getTreatmentBoundary()
    {
        return $this->treatmentBoundary;
    }

    /**
     * @return int
     */
    public function getNumberOfSeriousInjuries()
    {
        return $this->getUnhealedWounds()
            ->filter(
                function (Wound $wound) {
                    return $wound->isSerious();
                }
            )->count();
    }

    const DEADLY_NUMBER_OF_SERIOUS_INJURIES = 6;

    /**
     * @return bool
     */
    public function isAlive()
    {
        return
            $this->getRemainingHealthAmount() > 0
            && $this->getNumberOfSeriousInjuries() < self::DEADLY_NUMBER_OF_SERIOUS_INJURIES;
    }

    /**
     * @return bool
     */
    public function isConscious()
    {
        return $this->getGridOfWounds()->getNumberOfFilledRows() < GridOfWounds::UNCONSCIOUS_NUMBER_OF_ROWS;
    }

    /**
     *
     * @return int
     */
    public function getSignificantMalus()
    {
        $maluses = [$this->getMalusCausedByWounds()];
        foreach ($this->getPains() as $pain) {
            // for Pain see PPH page 79, left column
            $maluses[] = $pain->getEffect()->getMalusFromPain($pain);
        }

        return max($maluses);
    }

    /**
     * @return int
     */
    public function getMalusCausedByWounds()
    {
        if ($this->getGridOfWounds()->getNumberOfFilledRows() === 0) { // else even unconscious can has a malus (but would be wrong if applied)
            return 0;
        }

        if ($this->rollAgainstMalusFromWounds === null) {
            return 0; // no roll against malus happened so far, therefore no malus at all
        }

        /**
         * note: Can grow only on new wound when reach second row in grid of wounds.
         * Can decrease only on heal of any wound when on second row in grid of wounds.
         * Is removed when first row of grid of wounds is not filled.
         * See PPH page 75 right column
         */
        return $this->rollAgainstMalusFromWounds->getMalusValue();
    }

    /**
     * @var RollOnWillAgainstMalus|null
     */
    private $rollAgainstMalusFromWounds;

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return RollOnWillAgainstMalus
     */
    private function createRollAgainstMalusFromWounds(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        return new RollOnWillAgainstMalus(new RollOnWill($will, $roller2d6DrdPlus->roll()));
    }

    /**
     * @return array|Pain[]
     */
    public function getPains()
    {
        $pains = [];
        foreach ($this->getAfflictions() as $affliction) {
            if (!($affliction instanceof Pain)) {
                continue;
            }
            $pains[] = $affliction;
        }

        return $pains;
    }

}