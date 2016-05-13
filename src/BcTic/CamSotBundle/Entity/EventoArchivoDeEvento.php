<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventoArchivoDeEvento
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class EventoArchivoDeEvento
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
     * @ORM\ManyToOne(targetEntity="Evento", inversedBy="archivosDeEventos")
     * @ORM\JoinColumn(name="evento_id", referencedColumnName="id", onDelete="CASCADE")
     *
     */
    private $evento;

    /**
     * @var Evento
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="ArchivoDeEvento", cascade={"persist"})
     * @ORM\JoinColumn(name="archivo_de_evento_id", referencedColumnName="id")
     *
     */
    private $archivoDeEvento;


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
     * Set evento
     *
     * @param \BcTic\CamSotBundle\Entity\Evento $evento
     * @return EventoArchivoDeEvento
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
     * Set archivoDeEvento
     *
     * @param \BcTic\CamSotBundle\Entity\ArchivoDeEvento $archivoDeEvento
     * @return EventoArchivoDeEvento
     */
    public function setArchivoDeEvento(\BcTic\CamSotBundle\Entity\ArchivoDeEvento $archivoDeEvento = null)
    {
        $this->archivoDeEvento = $archivoDeEvento;

        return $this;
    }

    /**
     * Get archivoDeEvento
     *
     * @return \BcTic\CamSotBundle\Entity\ArchivoDeEvento 
     */
    public function getArchivoDeEvento()
    {
        return $this->archivoDeEvento;
    }
}
