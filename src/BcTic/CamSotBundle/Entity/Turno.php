<?php

namespace BcTic\CamSotBundle\Entity;

use BcTic\CamSotBundle\Entity\Periodo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Turno
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Turno
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="dia", type="string", length=25, nullable=false)
     */
    private $dia;

    /**
     * @var Periodo
     * 
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Periodo")
     * @ORM\JoinColumn(name="periodo_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({"inicio" = "asc"})
     */
    private $periodo;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dia
     *
     * @param string $dia
     * @return Turno
     */
    public function setDia($dia)
    {
        $this->dia = $dia;

        return $this;
    }

    /**
     * Get dia
     *
     * @return string 
     */
    public function getDia()
    {
        return $this->dia;
    }

    /**
     * Set periodo
     *
     * @param \BcTic\CamSotBundle\Entity\Periodo $periodo
     * @return Turno
     */
    public function setPeriodo(\BcTic\CamSotBundle\Entity\Periodo $periodo = null)
    {
        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get periodo
     *
     * @return \BcTic\CamSotBundle\Entity\Periodo 
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    public function __toString(){
        return "TIPO: ".str_replace(array('_'),array(' '),$this->dia).", PERIODO:".$this->periodo;
    }
}
