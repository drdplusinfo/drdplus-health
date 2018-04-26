<?php
namespace DrdPlus\Tests\Health;

use Doctrineum\Tests\Entity\AbstractDoctrineEntitiesTest;
use DrdPlus\Codes\Body\SeriousWoundOriginCode;
use DrdPlus\DiceRolls\Templates\Rollers\Roller2d6DrdPlus;
use DrdPlus\Codes\Properties\RemarkableSenseCode;
use DrdPlus\Codes\RaceCode;
use DrdPlus\Codes\SubRaceCode;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Cold;
use DrdPlus\Health\Afflictions\SpecificAfflictions\CrackedBones;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Hunger;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Health\Afflictions\SpecificAfflictions\SeveredArm;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Thirst;
use DrdPlus\Health\EnumTypes\HealthEnumsRegistrar;
use DrdPlus\Health\Health;
use DrdPlus\Health\WoundSize;
use DrdPlus\Lighting\Contrast;
use DrdPlus\Lighting\Glare;
use DrdPlus\Lighting\LightingQuality;
use DrdPlus\Properties\Base\Knack;
use DrdPlus\Properties\Base\Strength;
use DrdPlus\Properties\Derived\Senses;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\RollsOn\Traps\BonusFromUsedRemarkableSense;
use DrdPlus\RollsOn\Traps\RollOnSenses;
use DrdPlus\Tables\Tables;

class HealthDoctrineEntitiesTest extends AbstractDoctrineEntitiesTest
{
    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUp()
    {
        parent::setUp();
        HealthEnumsRegistrar::registerAll();
    }

    protected function getDirsWithEntities()
    {
        return [str_replace(DIRECTORY_SEPARATOR . 'Tests', '', __DIR__)];
    }

    protected function createEntitiesToPersist(): array
    {
        $health = new Health();
        $woundBoundary = WoundBoundary::getIt(
            Toughness::getIt(
                Strength::getIt(3),
                RaceCode::getIt(RaceCode::ORC),
                SubRaceCode::getIt(SubRaceCode::GOBLIN),
                Tables::getIt()
            ),
            Tables::getIt()
        );
        $ordinaryWound = $health->addWound(
            new WoundSize(1),
            SeriousWoundOriginCode::getMechanicalCutWoundOrigin(),
            $woundBoundary
        );
        $seriousWound = $health->addWound(
            new WoundSize(7),
            SeriousWoundOriginCode::getMechanicalCrushWoundOrigin(),
            $woundBoundary
        );
        $bleeding = Bleeding::createIt($seriousWound, $woundBoundary);
        $cold = Cold::createIt($seriousWound);
        $crackedBones = CrackedBones::createIt($seriousWound, $woundBoundary);
        $pain = Pain::createIt(
            $seriousWound,
            AfflictionVirulence::getDayVirulence(),
            AfflictionSize::getIt(5),
            WaterPertinence::getPlus()
        );
        $severedArm = SeveredArm::createIt($seriousWound);
        $hunger = Hunger::createIt($health, AfflictionSize::getIt(123));
        $thirst = Thirst::createIt($health, AfflictionSize::getIt(631));
        $health->inflictByGlare(
            new Glare(
                Contrast::createBySimplifiedRules(new LightingQuality(213), new LightingQuality(569)),
                new RollOnSenses(
                    Senses::getIt(
                        Knack::getIt(1),
                        $raceCode = RaceCode::getIt(RaceCode::ELF),
                        $subRaceCode = SubRaceCode::getIt(SubRaceCode::DARK),
                        Tables::getIt()
                    ),
                    Roller2d6DrdPlus::getIt()->roll(),
                    new BonusFromUsedRemarkableSense(
                        $raceCode,
                        $subRaceCode,
                        RemarkableSenseCode::getIt(RemarkableSenseCode::SIGHT),
                        Tables::getIt()
                    )
                ),
                false
            )
        );

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
            $hunger,
            $thirst,
            $health->getGlared(),
        ];
    }

}