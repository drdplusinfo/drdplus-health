<?php
namespace DrdPlus\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCode;

class WaterPertinence extends ElementalPertinence
{
    const WATER = ElementCode::WATER;

    /**
     * @return WaterPertinence|ElementalPertinence
     */
    public static function getMinus()
    {
        return parent::getMinus();
    }

    /**
     * @return WaterPertinence|ElementalPertinence
     */
    public static function getPlus()
    {
        return parent::getPlus();
    }

}