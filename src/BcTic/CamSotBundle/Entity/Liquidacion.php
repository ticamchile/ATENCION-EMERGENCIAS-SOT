<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Liquidacion
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Liquidacion
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
     * @Assert\NotBlank()
     * @ORM\Column(name="descripcion", type="text")
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=25)
     */
    private $status = 'PENDING';

    /**
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="ArchivoDeCierre")
     * @ORM\JoinColumn(name="archivo_de_cierre_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $archivoDeCierre;

    /**
     * @var string
     *
     * @ORM\Column(name="indice", type="string", length=50)
     */
    private $indice = '';

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
     * Set descripcion
     *
     * @param string $descripcion
     * @return Liquidacion
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
     * @return Liquidacion
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
     * Set archivoDeCierre
     *
     * @param \stdClass $archivoDeCierre
     * @return Liquidacion
     */
    public function setArchivoDeCierre($archivoDeCierre)
    {
        $this->archivoDeCierre = $archivoDeCierre;

        return $this;
    }

    /**
     * Get archivoDeCierre
     *
     * @return \stdClass 
     */
    public function getArchivoDeCierre()
    {
        return $this->archivoDeCierre;
    }

    /**
     * Set indice
     *
     * @param string $indice
     * @return Liquidacion
     */
    public function setIndice($indice)
    {
        $this->indice = $indice;

        return $this;
    }

    /**
     * Get indice
     *
     * @return string 
     */
    public function getIndice()
    {
        return $this->indice;
    }
}
