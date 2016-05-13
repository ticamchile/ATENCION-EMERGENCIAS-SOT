<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * CuadrillaDeEvento
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class CuadrillaDeEvento
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
     * @var string
     *
     * @ORM\Column(name="valor", type="string", length=255)
     */
    private $valor = "PENDIENTE";

   /** 
    * @ORM\OneToMany(targetEntity="PropiedadDeCuadrillaDeEvento", mappedBy="cuadrillaDeEvento", cascade={"all"}, indexBy="nemo") 
    */
    private $propiedadesDeCuadrillaDeEvento;

    /**
     * @ORM\OneToMany(targetEntity="PromesaDeEvento", mappedBy="cuadrillaDeEvento", cascade={"all"}, indexBy="nemo") 
     */  
    private $promesasDeEvento;

    public function getPromesaDeEvento($nemo)
    {
       if (!isset($this->promesasDeEvento[$nemo])) {
           $obj = new PromesaDeEvento();
           $obj->setNemo($nemo);
           return $obj;
        }

        return $this->promesasDeEvento[$nemo];
    } 




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
     * @return CuadrillaDeEvento
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
     * @return CuadrillaDeEvento
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
     * Constructor
     */
    public function __construct()
    {
        $this->propiedadesDeCuadrillaDeEvento = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add propiedadesDeCuadrillaDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\PropiedadDeCuadrillaDeEvento $propiedadesDeCuadrillaDeEvento
     * @return CuadrillaDeEvento
     */
    public function addPropiedadesDeCuadrillaDeEvento(\BcTic\CamSotBundle\Entity\PropiedadDeCuadrillaDeEvento $propiedadesDeCuadrillaDeEvento)
    {
        $this->propiedadesDeCuadrillaDeEvento[] = $propiedadesDeCuadrillaDeEvento;

        return $this;
    }

    /**
     * Remove propiedadesDeCuadrillaDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\PropiedadDeCuadrillaDeEvento $propiedadesDeCuadrillaDeEvento
     */
    public function removePropiedadesDeCuadrillaDeEvento(\BcTic\CamSotBundle\Entity\PropiedadDeCuadrillaDeEvento $propiedadesDeCuadrillaDeEvento)
    {
        $this->propiedadesDeCuadrillaDeEvento->removeElement($propiedadesDeCuadrillaDeEvento);
    }

    /**
     * Get propiedadesDeCuadrillaDeEvento
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPropiedadesDeCuadrillaDeEvento()
    {
        return $this->propiedadesDeCuadrillaDeEvento;
    }

     public function getPropiedadCuadrillaDeEvento($nemo)
    {
       if (!isset($this->propiedadesDeCuadrillaDeEvento[$nemo])) {
           $obj = new PropiedadDeCuadrillaDeEvento();
           $obj->setNemo($nemo);
           return $obj;
        }
        return $this->propiedadesDeCuadrillaDeEvento[$nemo];
    }    

    /**
     * Add promesasDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\PromesaDeEvento $promesasDeEvento
     * @return CuadrillaDeEvento
     */
    public function addPromesasDeEvento(\BcTic\CamSotBundle\Entity\PromesaDeEvento $promesasDeEvento)
    {
        $this->promesasDeEvento[] = $promesasDeEvento;

        return $this;
    }

    /**
     * Remove promesasDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\PromesaDeEvento $promesasDeEvento
     */
    public function removePromesasDeEvento(\BcTic\CamSotBundle\Entity\PromesaDeEvento $promesasDeEvento)
    {
        $this->promesasDeEvento->removeElement($promesasDeEvento);
    }

    /**
     * Get promesasDeEvento
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPromesasDeEvento()
    {
        return $this->promesasDeEvento;
    }
}
