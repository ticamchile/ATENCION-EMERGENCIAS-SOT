<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArchivoDeCierre
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ArchivoDeCierre
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
     * @ORM\Column(name="fechaDeInicio", type="date")
     */
    private $fechaDeInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fechaDeTermino", type="date")
     */
    private $fechaDeTermino;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="descripcion", type="string", length=255)
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=25)
     */
    private $status = "PENDING"; //PENDING, PROCESSING, DONE, HAS_CONFORMITY, ERROR

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;  

    /**
     * 
     *
     * @ORM\Column(name="createdAt", type="integer")
     */
    private $createdAt;

    public function __construct(){
        $date = new \DateTime();
        $this->createdAt = $date->format('U');

        //El inicio del mes:
        $this->fechaDeInicio = \DateTime::createFromFormat('Y-m-d',date('Y').'-'.date('m').'-01');
        $this->fechaDeTermino = \DateTime::createFromFormat('Y-m-d',date('Y').'-'.date('m').'-31');

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
     * Set fechaDeInicio
     *
     * @param \DateTime $fechaDeInicio
     * @return ArchivoDeCierre
     */
    public function setFechaDeInicio($fechaDeInicio)
    {
        $this->fechaDeInicio = $fechaDeInicio;

        return $this;
    }

    /**
     * Get fechaDeInicio
     *
     * @return \DateTime 
     */
    public function getFechaDeInicio()
    {
        return $this->fechaDeInicio;
    }

    /**
     * Set fechaDeTermino
     *
     * @param \DateTime $fechaDeTermino
     * @return ArchivoDeCierre
     */
    public function setFechaDeTermino($fechaDeTermino)
    {
        $this->fechaDeTermino = $fechaDeTermino;

        return $this;
    }

    /**
     * Get fechaDeTermino
     *
     * @return \DateTime 
     */
    public function getFechaDeTermino()
    {
        return $this->fechaDeTermino;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return ArchivoDeCierre
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
     * Set createdAt
     *
     * @param integer $createdAt
     * @return ArchivoDeCierre
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
     * Set status
     *
     * @param string $status
     * @return ArchivoDeCierre
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
     * Set path
     *
     * @param string $path
     * @return ArchivoDeCierre
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    public function __toString() {
      return $this->descripcion.' - DESDE: '.$this->fechaDeInicio->format('d-m-Y').' HASTA: '.$this->fechaDeTermino->format('d-m-Y');
    }
}
