<?php
namespace DrdPlus\Health\Afflictions\SpecificAfflictions;

use Doctrine\ORM\Mapping as ORM;
use DrdPlus\Health\Afflictions\Affliction;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\HungerEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\EarthPertinence;
use DrdPlus\Health\Health;
use DrdPlus\Calculations\SumAndRound;

/**
 * @ORM\Entity
 */
class Hunger extends Affliction
{

    /**
     * @param Health $health
     * @param AfflictionSize $daysOfHunger
     * @return Hunger
     * @throws \DrdPlus\Health\Exceptions\UnknownAfflictionOriginatingWound
     * @throws \DrdPlus\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    public static function createIt(Health $health, AfflictionSize $daysOfHunger): Hunger
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $health,
            AfflictionProperty::getIt(AfflictionProperty::ENDURANCE), // irrelevant, hunger can not be avoided
            AfflictionDangerousness::getIt(9999), // irrelevant, hunger can not be avoided
            AfflictionDomain::getPhysicalDomain(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getPassiveSource(),
            $daysOfHunger,
            EarthPertinence::getMinus(),
            HungerEffect::getIt(),
            new \DateInterval('P1D'),
            AfflictionName::getIt('hunger')
        );
    }

    /**
     * @return int
     */
    public function getHealMalus(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getMalusToActivities(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getStrengthMalus(): int
    {
        return -SumAndRound::half($this->getAfflictionSize()->getValue());
    }

    /**
     * @return int
     */
    public function getAgilityMalus(): int
    {
        return -SumAndRound::half($this->getAfflictionSize()->getValue());
    }

    /**
     * @return int
     */
    public function getKnackMalus(): int
    {
        return -SumAndRound::half($this->getAfflictionSize()->getValue());
    }

    /**
     * @return int
     */
    public function getWillMalus(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getIntelligenceMalus(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getCharismaMalus(): int
    {
        return 0;
    }
}