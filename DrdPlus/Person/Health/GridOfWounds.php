<?php
namespace DrdPlus\Person\Health;

use DrdPlus\Tools\Calculations\SumAndRound;
use Granam\Strict\Object\StrictObject;

class GridOfWounds extends StrictObject
{

    const PAIN_NUMBER_OF_ROWS = 1;
    const UNCONSCIOUS_NUMBER_OF_ROWS = 2;
    const TOTAL_NUMBER_OF_ROWS = 3;

    /**
     * @var Health
     */
    private $health;

    public function __construct(Health $health)
    {
        $this->health = $health;
    }

    /**
     * @return int
     */
    public function getSumOfWounds()
    {
        return array_sum(
            array_map(
                function (PointOfWound $pointOfWound) {
                    return $pointOfWound->getValue();
                },
                $this->getPointsOfWounds()
            )
        );
    }

    /**
     * @return array|PointOfWound[]
     */
    private function getPointsOfWounds()
    {
        return array_merge(
            array_map(
                function (Wound $wound) {
                    $wound->getPointsOfWound();
                },
                $this->health->getUnhealedWounds()
            )
        );
    }

    /**
     * @return int
     */
    public function getWoundsPerRowMaximum()
    {
        return $this->health->getWoundsLimitValue();
    }

    /**
     * @param int $woundValue
     * @return int
     */
    public function calculateFilledHalfRowsFor($woundValue)
    {
        if ($this->getWoundsPerRowMaximum() % 2 === 0) { // odd
            return SumAndRound::floor($woundValue / ($this->getWoundsPerRowMaximum() / 2));
        }
        // first half round up, second down (for example 11 = 6 + 5)
        $halves = [SumAndRound::ceiledHalf($this->getWoundsPerRowMaximum()), SumAndRound::flooredHalf($this->getWoundsPerRowMaximum())];
        $numberOfHalfRows = 0;
        while ($woundValue > 0) {
            foreach ($halves as $half) {
                $woundValue -= $half;
                if ($woundValue < 0) {
                    break;
                }
                $numberOfHalfRows++;
            }
        }

        return $numberOfHalfRows;
    }

    /**
     * @param int $woundValue
     * @return bool
     */
    public function isSeriousInjury($woundValue)
    {
        return $this->calculateFilledHalfRowsFor($woundValue) > 0;
    }

    /**
     * @return int
     */
    public function getRemainingHealth()
    {
        return max($this->getHealthMaximum() - $this->getSumOfWounds(), 0);
    }

    /**
     * @return int
     */
    public function getHealthMaximum()
    {
        return $this->health->getWoundsLimitValue() * self::TOTAL_NUMBER_OF_ROWS;
    }

    /**
     * @return int
     */
    public function getNumberOfFilledRows()
    {
        return SumAndRound::floor($this->getSumOfWounds() / $this->getWoundsPerRowMaximum());
    }

}