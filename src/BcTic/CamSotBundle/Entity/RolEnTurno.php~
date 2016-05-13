<?php

namespace BcTic\CamSotBundle\Entity;

use BcTic\CamSotBundle\Entity\RolDeTurno;
use BcTic\CamSotBundle\Entity\Rol;
use BcTic\CamSotBundle\Entity\Registro;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * RolEnTurno
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class RolEnTurno
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
     * @var Rol
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Rol")
     * @ORM\JoinColumn(name="rol_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({})
     */
    private $rol;

    /**
     * @var Registro
     *
     * 
     * @ORM\ManyToOne(targetEntity="Registro")
     * @ORM\JoinColumn(name="registro_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({ "nombre" = "DESC"})
     */
    private $registro;

     /**
     * @var RolDeTurno
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="RolDeTurno", inversedBy="rolesEnTurno")
     * @ORM\JoinColumn(name="rol_de_turno_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({})
     */
    private $rolDeTurno;


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
     * Set rol
     *
     * @param \BcTic\CamSotBundle\Entity\Rol $rol
     * @return RolEnTurno
     */
    public function setRol(\BcTic\CamSotBundle\Entity\Rol $rol = null)
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return \BcTic\CamSotBundle\Entity\Rol 
     */
    public function getRol()
    {
        return $this->rol;
    }

    /**
     * Set registro
     *
     * @param \BcTic\CamSotBundle\Entity\Registro $registro
     * @return RolEnTurno
     */
    public function setRegistro(\BcTic\CamSotBundle\Entity\Registro $registro = null)
    {
        $this->registro = $registro;

        return $this;
    }

    /**
     * Get registro
     *
     * @return \BcTic\CamSotBundle\Entity\Registro 
     */
    public function getRegistro()
    {
        return $this->registro;
    }

    /**
     * Set rolDeTurno
     *
     * @param \BcTic\CamSotBundle\Entity\RolDeTurno $rolDeTurno
     * @return RolEnTurno
     */
    public function setRolDeTurno(\BcTic\CamSotBundle\Entity\RolDeTurno $rolDeTurno = null)
    {
        $this->rolDeTurno = $rolDeTurno;

        return $this;
    }

    /**
     * Get rolDeTurno
     *
     * @return \BcTic\CamSotBundle\Entity\RolDeTurno 
     */
    public function getRolDeTurno()
    {
        return $this->rolDeTurno;
    }
}
