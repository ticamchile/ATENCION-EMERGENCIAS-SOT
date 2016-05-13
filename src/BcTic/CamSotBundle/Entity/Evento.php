<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Evento
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Evento
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
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="integer")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=8)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=25)
     */
    private $status = "NEW";

    /** 
    * @ORM\OneToMany(targetEntity="PropiedadDeEvento", mappedBy="evento", cascade={"all"}, indexBy="nemo") 
    */
    private $propiedadesDeEvento;

    /** 
    * @ORM\OneToMany(targetEntity="CausaDeEvento", mappedBy="evento", cascade={"all"}, indexBy="nemo") 
    */
    private $causasDeEvento;

    /**
     * @ORM\OneToMany(targetEntity="EventoArchivoDeEvento", mappedBy="evento", cascade={"all"}) 
     */  
    private $archivosDeEventos;

    /**
     * @ORM\OneToMany(targetEntity="CuadrillaDeEvento", mappedBy="evento", cascade={"all"}, indexBy="nemo") 
     */  
    private $cuadrillasDeEvento;

    /**
     * @ORM\OneToMany(targetEntity="Historial", mappedBy="evento", cascade={"all"}) 
     */  
    private $historiales;    

    //COLUMNA 8 - Fecha de inicio
    private $fechaDeInicio;

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
     * Set nombre
     *
     * @param string $nombre
     * @return Evento
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Evento
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Evento
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $date = new \DateTime();
        $this->createdAt = $date->format('U');
        $this->fechaDeInicio = $date;
        $this->propiedadesDeEvento = new \Doctrine\Common\Collections\ArrayCollection();
        $this->archivosDeEventos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->causasDeEventos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cuadrillasDeEventos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->historiales = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add archivosDeEventos
     *
     * @param \BcTic\CamSotBundle\Entity\EventoArchivoDeEvento $archivosDeEventos
     * @return Evento
     */
    public function addArchivosDeEvento(\BcTic\CamSotBundle\Entity\EventoArchivoDeEvento $archivosDeEventos)
    {
        $this->archivosDeEventos[] = $archivosDeEventos;

        return $this;
    }

    /**
     * Remove archivosDeEventos
     *
     * @param \BcTic\CamSotBundle\Entity\EventoArchivoDeEvento $archivosDeEventos
     */
    public function removeArchivosDeEvento(\BcTic\CamSotBundle\Entity\EventoArchivoDeEvento $archivosDeEventos)
    {
        $this->archivosDeEventos->removeElement($archivosDeEventos);
    }

    /**
     * Get archivosDeEventos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArchivosDeEventos()
    {
        return $this->archivosDeEventos;
    }

    /**
     * Set createdAt
     *
     * @param integer $createdAt
     * @return Evento
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return integer 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get Fecha de Inicio
     *
     * @return string 
     */
    public function getFechaDeInicio()
    {
        return $this->fechaDeInicio;
    }

    /**
     * Add causasDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\CausaDeEvento $causasDeEvento
     * @return Evento
     */
    public function addCausasDeEvento(\BcTic\CamSotBundle\Entity\CausaDeEvento $causaDeEvento)
    {
        $this->causasDeEvento[$causaDeEvento->getNemo()] = $causaDeEvento;

        return $this;
    }

    /**
     * Remove causasDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\CausaDeEvento $causasDeEvento
     */
    public function removeCausasDeEvento(\BcTic\CamSotBundle\Entity\CausaDeEvento $causaDeEvento)
    {
        $this->causasDeEvento->removeElement($causaDeEvento);
    }

    /**
     * Get causasDeEvento
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCausasDeEvento()
    {
        return $this->causasDeEvento;
    }


    /**
     * Add cuadrillasDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\CuadrillaDeEvento $cuadrillasDeEvento
     * @return Evento
     */
    public function addCuadrillasDeEvento(\BcTic\CamSotBundle\Entity\CuadrillaDeEvento $cuadrillaDeEvento)
    {
        $this->cuadrillasDeEvento[$cuadrillaDeEvento->getNemo()] = $cuadrillaDeEvento;

        return $this;
    }

    /**
     * Remove cuadrillasDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\CuadrillaDeEvento $cuadrillasDeEvento
     */
    public function removeCuadrillasDeEvento(\BcTic\CamSotBundle\Entity\CuadrillaDeEvento $cuadrillaDeEvento)
    {
        $this->cuadrillasDeEvento->removeElement($cuadrillaDeEvento);
    }

    /**
     * Get cuadrillasDeEvento
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCuadrillasDeEvento()
    {
        return $this->cuadrillasDeEvento;
    }

    /**
     * Add propiedadesDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\PropiedadDeEvento $propiedadesDeEvento
     * @return Evento
     */
    public function addPropiedadesDeEvento(\BcTic\CamSotBundle\Entity\PropiedadDeEvento $propiedadDeEvento)
    {
        $this->propiedadesDeEvento[$propiedadDeEvento->getNemo()] = $propiedadDeEvento;

        return $this;
    }

    /**
     * Remove propiedadesDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\PropiedadDeEvento $propiedadesDeEvento
     */
    public function removePropiedadesDeEvento(\BcTic\CamSotBundle\Entity\PropiedadDeEvento $propiedadDeEvento)
    {
        $this->propiedadesDeEvento->removeElement($propiedadDeEvento);
    }

    /**
     * Get propiedadesDeEvento
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPropiedadesDeEvento()
    {
        return $this->propiedadesDeEvento->toArray();
    }

    public function getPropiedadDeEvento($nemo)
    {
       if (!isset($this->propiedadesDeEvento[$nemo])) {
           $obj = new PropiedadDeEvento();
           $obj->setNemo($nemo);
           return $obj;
        }
        return $this->propiedadesDeEvento[$nemo];
    }    

    public function getCuadrillaDeEvento($nemo)
    {
       if (!isset($this->cuadrillasDeEvento[$nemo])) {
           $obj = new CuadrillaDeEvento();
           $obj->setNemo($nemo);
           return $obj;
        }

        return $this->cuadrillasDeEvento[$nemo];
    } 

    public function getCausaDeEvento($nemo)
    {
       if (!isset($this->causasDeEvento[$nemo])) {
           $obj = new CausaDeEvento();
           $obj->setNemo($nemo);
           return $obj;
        }

        return $this->causasDeEvento[$nemo];
    } 

    /**
     * Add historiales
     *
     * @param \BcTic\CamSotBundle\Entity\Historial $historiales
     * @return Evento
     */
    public function addHistorial(\BcTic\CamSotBundle\Entity\Historial $historial)
    {
        $this->historiales[] = $historial;

        return $this;
    }

    /**
     * Remove historiales
     *
     * @param \BcTic\CamSotBundle\Entity\Historial $historiales
     */
    public function removeHistorial(\BcTic\CamSotBundle\Entity\Historial $historial)
    {
        $this->historiales->removeElement($historial);
    }

    /**
     * Get historiales
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHistoriales()
    {
        return $this->historiales;
    }

    /**
     * Add historiales
     *
     * @param \BcTic\CamSotBundle\Entity\Historial $historiales
     * @return Evento
     */
    public function addHistoriale(\BcTic\CamSotBundle\Entity\Historial $historiales)
    {
        $this->historiales[] = $historiales;

        return $this;
    }

    /**
     * Remove historiales
     *
     * @param \BcTic\CamSotBundle\Entity\Historial $historiales
     */
    public function removeHistoriale(\BcTic\CamSotBundle\Entity\Historial $historiales)
    {
        $this->historiales->removeElement($historiales);
    }
}
