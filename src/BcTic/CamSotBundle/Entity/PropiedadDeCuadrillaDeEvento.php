<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * PropiedadDeCuadrillaDeEvento
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PropiedadDeCuadrillaDeEvento
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
     * @var CuadrillaDeEvento
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="CuadrillaDeEvento")
     * @ORM\JoinColumn(name="cuadrilla_de_evento_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({"nemo" = "asc"})
     */
    private $cuadrillaDeEvento;

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
    private $valor;


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

    /**
     * Set cuadrillaDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\CuadrillaDeEvento $cuadrillaDeEvento
     * @return PropiedadDeCuadrillaDeEvento
     */
    public function setCuadrillaDeEvento(\BcTic\CamSotBundle\Entity\CuadrillaDeEvento $cuadrillaDeEvento = null)
    {
        $this->cuadrillaDeEvento = $cuadrillaDeEvento;

        return $this;
    }

    /**
     * Get cuadrillaDeEvento
     *
     * @return \BcTic\CamSotBundle\Entity\CuadrillaDeEvento 
     */
    public function getCuadrillaDeEvento()
    {
        return $this->cuadrillaDeEvento;
    }
}
