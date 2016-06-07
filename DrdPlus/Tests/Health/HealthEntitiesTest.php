<?php
namespace DrdPlus\Tests\Health;

use Doctrineum\Tests\Entity\AbstractDoctrineEntitiesTest;
use DrdPlus\Codes\RaceCodes;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Cold;
use DrdPlus\Health\Afflictions\SpecificAfflictions\CrackedBones;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Health\Afflictions\SpecificAfflictions\SeveredArm;
use DrdPlus\Health\EnumTypes\HealthEnumsRegistrar;
use DrdPlus\Health\Health;
use DrdPlus\Health\SpecificWoundOrigin;
use DrdPlus\Health\WoundSize;
use DrdPlus\Properties\Base\Strength;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use DrdPlus\Tables\Races\RacesTable;

class HealthEntitiesTest extends AbstractDoctrineEntitiesTest
{
    protected function setUp()
    {
        parent::setUp();
        HealthEnumsRegistrar::registerAll();
    }

    protected function getDirsWithEntities()
    {
        return [
            str_replace(DIRECTORY_SEPARATOR . 'Tests', '', __DIR__)
        ];
    }

    protected function createEntitiesToPersist()
    {
        $health = new Health(
            new WoundBoundary(
                new Toughness(new Strength(3), RaceCodes::ORC, RaceCodes::GOBLIN, new RacesTable()),
                new WoundsTable()
            )
        );
        $ordinaryWound = $health->createWound(new WoundSize(1), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        $seriousWound = $health->createWound(new WoundSize(7), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        $bleeding = Bleeding::createIt($seriousWound);
        $cold = Cold::createIt($seriousWound);
        $crackedBones = CrackedBones::createIt($seriousWound);
        $pain = Pain::createIt($seriousWound, AfflictionVirulence::getDayVirulence(), AfflictionSize::getIt(5), WaterPertinence::getPlus());
        $severedArm = SeveredArm::createIt($seriousWound);

        return [
            $health,
            $ordinaryWound,
            $seriousWound,
            $ordinaryWound->getPointsOfWound()->last(),
            $bleeding,
            $cold,
            $crackedBones,
            $pain,
            $severedArm,
        ];
    }

}