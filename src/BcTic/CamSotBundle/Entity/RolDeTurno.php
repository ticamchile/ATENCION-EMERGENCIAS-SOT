<?php

namespace BcTic\CamSotBundle\Entity;


use BcTic\CamSotBundle\Entity\Periodo;
use BcTic\CamSotBundle\Entity\RolEnTurno;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * RolDeTurno
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class RolDeTurno
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
     * @var Turno
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Turno")
     * @ORM\JoinColumn(name="turno_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({"dia" = "asc"})
     */
    private $turno;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date")
     */
    private $fecha;

    /** 
    * @ORM\OneToMany(targetEntity="RolEnTurno", mappedBy="rolDeTurno", cascade={"all"}) 
    */
    protected $rolesEnTurno;


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
     * Set turno
     *
     * @param \stdClass $turno
     * @return RolDeTurno
     */
    public function setTurno(Turno $turno)
    {
        $this->turno = $turno;

        return $this;
    }

    /**
     * Get turno
     *
     * @return \stdClass 
     */
    public function getTurno()
    {
        return $this->turno;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return RolDeTurno
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }


    public function __toString(){
        $string = "";
        foreach ($this->getRolesEnTurno() as $rol) {
          $string .= ($rol->getRegistro() != null) ? $rol->getRol().':'.$rol->getRegistro()." " : "";
        }
        return $string;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rolesEnTurno = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add rolesEnTurno
     *
     * @param \BcTic\CamSotBundle\Entity\RolEnTurno $rolesEnTurno
     * @return RolDeTurno
     */
    public function addRolesEnTurno(\BcTic\CamSotBundle\Entity\RolEnTurno $rolesEnTurno)
    {
        $this->rolesEnTurno[] = $rolesEnTurno;

        return $this;
    }

    /**
     * Remove rolesEnTurno
     *
     * @param \BcTic\CamSotBundle\Entity\RolEnTurno $rolesEnTurno
     */
    public function removeRolesEnTurno(\BcTic\CamSotBundle\Entity\RolEnTurno $rolesEnTurno)
    {
        $this->rolesEnTurno->removeElement($rolesEnTurno);
    }

    /**
     * Get rolesEnTurno
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRolesEnTurno()
    {
        return $this->rolesEnTurno;
    }
}
