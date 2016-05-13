<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * PropiedadDeEvento
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PropiedadDeEvento
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
     * @var Evento
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Evento")
     * @ORM\JoinColumn(name="evento_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({"nemo" = "asc"})
     */
    private $evento;

    /**
     * @var integer
     *
     * @ORM\Column(name="nemo", type="integer")
     */
    private $nemo = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="string", length=255)
     */
    private $valor = "-";


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
     * Set valor
     *
     * @param string $valor
     * @return PropiedadDeEvento
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string 
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set evento
     *
     * @param \BcTic\CamSotBundle\Entity\Evento $evento
     * @return PropiedadDeEvento
     */
    public function setEvento(\BcTic\CamSotBundle\Entity\Evento $evento = null)
    {
        $this->evento = $evento;

        return $this;
    }

    /**
     * Get evento
     *
     * @return \BcTic\CamSotBundle\Entity\Evento 
     */
    public function getEvento()
    {
        return $this->evento;
    }

    /**
     * Set nemo
     *
     * @param integer $nemo
     * @return PropiedadDeEvento
     */
    public function setNemo($nemo)
    {
        $this->nemo = $nemo;

        return $this;
    }

    /**
     * Get nemo
     *
     * @return integer 
     */
    public function getNemo()
    {
        return $this->nemo;
    }
}
