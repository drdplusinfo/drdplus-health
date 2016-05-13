<?php
namespace DrdPlus\Person\Health\Afflictions;

use Doctrine\ORM\Mapping as ORM;
use Doctrineum\Entity\Entity;
use Doctrineum\Integer\IntegerEnum;
use DrdPlus\Person\Health\Afflictions\Effects\AfflictionEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Person\Health\Wound;
use Granam\Strict\Object\StrictObject;

abstract class AfflictionByWound extends StrictObject implements Entity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var Wound|null
     * @ORM\ManyToOne(targetEntity="\DrdPlus\Person\Health\Wound", cascade={"persist"}, inversedBy="afflictions")
     */
    private $wound;
    /**
     * @var AfflictionDomain
     * @ORM\Column(type="affliction_domain")
     */
    private $domain;
    /**
     * @var AfflictionVirulence
     * @ORM\Column(type="virulence")
     */
    private $virulence;
    /**
     * @var AfflictionSource
     */
    private $source;
    /**
     * @var AfflictionProperty
     * @ORM\Column(type="affliction_property")
     */
    private $property;
    /**
     * @var AfflictionDangerousness
     * @ORM\Column(type="affliction_dangerousness")
     */
    private $dangerousness;
    /**
     * @var AfflictionSize
     * @ORM\Column(type="affliction_size")
     */
    private $size;
    /**
     * @var ElementalPertinence
     * @ORM\Column(type="elemental_pertinence")
     */
    private $elementalPertinence;
    /**
     * @var AfflictionEffect
     * @ORM\Column(type="affliction_effect")
     */
    private $effect;
    /**
     * @var \DateInterval
     * @ORM\Column(type="date_interval")
     */
    private $outbreakPeriod;

    /**
     * @var AfflictionName
     * @ORM\Column(type="affliction_name")
     */
    private $afflictionName;

    /**
     * @param Wound $wound
     * @param AfflictionDomain $domain
     * @param AfflictionVirulence $virulence
     * @param AfflictionSource $source
     * @param AfflictionProperty $property
     * @param AfflictionDangerousness $dangerousness
     * @param AfflictionSize $size
     * @param ElementalPertinence $elementalPertinence
     * @param AfflictionEffect $effect
     * @param \DateInterval $outbreakPeriod
     * @param $afflictionName
     */
    protected function __construct(
        Wound $wound, // wound can be healed, but never disappears - just stays healed
        AfflictionDomain $domain,
        AfflictionVirulence $virulence,
        AfflictionSource $source,
        AfflictionProperty $property,
        AfflictionDangerousness $dangerousness,
        AfflictionSize $size,
        ElementalPertinence $elementalPertinence,
        AfflictionEffect $effect,
        \DateInterval $outbreakPeriod,
        AfflictionName $afflictionName
    )
    {
        $this->wound = $wound;
        $this->domain = $domain;
        $this->virulence = $virulence;
        $this->source = $source;
        $this->property = $property;
        $this->dangerousness = $dangerousness;
        $this->size = $size;
        $this->elementalPertinence = $elementalPertinence;
        $this->effect = $effect;
        $this->outbreakPeriod = $outbreakPeriod;
        $this->afflictionName = $afflictionName;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Wound|null
     */
    public function getWound()
    {
        return $this->wound;
    }

    /**
     * @return AfflictionDomain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return AfflictionVirulence
     */
    public function getVirulence()
    {
        return $this->virulence;
    }

    /**
     * @return AfflictionSource
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return AfflictionProperty
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return IntegerEnum
     */
    public function getDangerousness()
    {
        return $this->dangerousness;
    }

    /**
     * @return AfflictionSize
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return ElementalPertinence
     */
    public function getElementalPertinence()
    {
        return $this->elementalPertinence;
    }

    /**
     * @return AfflictionEffect
     */
    public function getEffect()
    {
        return $this->effect;
    }

    /**
     * @return \DateInterval
     */
    public function getOutbreakPeriod()
    {
        return $this->outbreakPeriod;
    }

    /**
     * @return string
     */
    public function getAfflictionName()
    {
        return $this->afflictionName;
    }

}