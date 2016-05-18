<?php
namespace DrdPlus\Tests\Person\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Person\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Person\Health\Afflictions\AfflictionDomain;
use DrdPlus\Person\Health\Afflictions\AfflictionName;
use DrdPlus\Person\Health\Afflictions\AfflictionProperty;
use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\AfflictionSource;
use DrdPlus\Person\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Person\Health\Afflictions\Effects\SeveredArmEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\EarthPertinence;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\SeveredArm;
use DrdPlus\Tests\Person\Health\Afflictions\AfflictionByWoundTest;

class SeveredArmTest extends AfflictionByWoundTest
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $severedArm = SeveredArm::createIt($wound = $this->createWound());

        self::assertNull($severedArm->getId());
        self::assertSame($wound, $severedArm->getWound());

        self::assertInstanceOf(AfflictionDomain::class, $severedArm->getDomain());
        self::assertSame('physical', $severedArm->getDomain()->getValue());

        self::assertInstanceOf(AfflictionVirulence::class, $severedArm->getVirulence());
        self::assertSame(AfflictionVirulence::DAY, $severedArm->getVirulence()->getValue());

        self::assertInstanceOf(AfflictionSource::class, $severedArm->getSource());
        self::assertSame(AfflictionSource::FULL_DEFORMATION, $severedArm->getSource()->getValue());

        self::assertInstanceOf(AfflictionProperty::class, $severedArm->getProperty());

        self::assertInstanceOf(AfflictionDangerousness::class, $severedArm->getDangerousness());

        self::assertInstanceOf(AfflictionSize::class, $severedArm->getSize());
        self::assertSame(6 /* by default*/, $severedArm->getSize()->getValue());

        self::assertInstanceOf(EarthPertinence::class, $severedArm->getElementalPertinence());
        self::assertTrue($severedArm->getElementalPertinence()->isMinus());

        self::assertInstanceOf(SeveredArmEffect::class, $severedArm->getEffect());

        self::assertInstanceOf(\DateInterval::class, $severedArm->getOutbreakPeriod());
        self::assertSame('0y0m0d0h0i0s', $severedArm->getOutbreakPeriod()->format('%yy%mm%dd%hh%ii%ss'));

        self::assertInstanceOf(AfflictionName::class, $severedArm->getName());
        self::assertSame('completely_severed_arm', $severedArm->getName()->getValue());
    }

    /**
     * @test
     */
    public function I_can_create_partially_severed_arm()
    {
        $severedArm = SeveredArm::createIt($this->createWound(), $sizeValue = 1);

        self::assertInstanceOf(AfflictionDomain::class, $severedArm->getDomain());
        self::assertSame('physical', $severedArm->getDomain()->getValue());

        self::assertInstanceOf(AfflictionVirulence::class, $severedArm->getVirulence());
        self::assertSame(AfflictionVirulence::DAY, $severedArm->getVirulence()->getValue());

        self::assertInstanceOf(AfflictionSource::class, $severedArm->getSource());
        self::assertSame(AfflictionSource::FULL_DEFORMATION, $severedArm->getSource()->getValue());

        self::assertInstanceOf(AfflictionProperty::class, $severedArm->getProperty());

        self::assertInstanceOf(AfflictionDangerousness::class, $severedArm->getDangerousness());

        self::assertInstanceOf(AfflictionSize::class, $severedArm->getSize());
        self::assertSame($sizeValue, $severedArm->getSize()->getValue());

        self::assertInstanceOf(EarthPertinence::class, $severedArm->getElementalPertinence());
        self::assertTrue($severedArm->getElementalPertinence()->isMinus());

        self::assertInstanceOf(SeveredArmEffect::class, $severedArm->getEffect());

        self::assertInstanceOf(\DateInterval::class, $severedArm->getOutbreakPeriod());
        self::assertSame('0y0m0d0h0i0s', $severedArm->getOutbreakPeriod()->format('%yy%mm%dd%hh%ii%ss'));

        self::assertInstanceOf(AfflictionName::class, $severedArm->getName());
        self::assertSame('severed_arm', $severedArm->getName()->getValue());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Exceptions\SeveredArmAfflictionSizeExceeded
     */
    public function I_can_not_create_more_than_completely_severed_arm()
    {
        SeveredArm::createIt($this->createWound(), 7);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative
     */
    public function I_can_not_create_severed_arm_with_negative_value()
    {
        try {
            SeveredArm::createIt($this->createWound(), 0);
        } catch (\Exception $e) {
            self::fail('No exception expected so far: ' . $e->getTraceAsString());
        }

        SeveredArm::createIt($this->createWound(), -1);
    }

}
