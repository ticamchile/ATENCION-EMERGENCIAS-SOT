<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Registro
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Registro
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
     * @ORM\Column(name="nombre", type="string", length=50)
     */
    private $nombre;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="nemo", type="string", length=25)
     */
    private $nemo;

    /**
     * @var string
     *
     * 
     * @ORM\Column(name="dni", type="string", length=25)
     */
    private $dni;

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
     * @return Registro
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
     * Set nemo
     *
     * @param string $nemo
     * @return Registro
     */
    public function setNemo($nemo)
    {
        $this->nemo = $nemo;

        return $this;
    }

    /**
     * Get nemo
     *
     * @return string 
     */
    public function getNemo()
    {
        return $this->nemo;
    }

    public function __toString(){
      return $this->getNombre();
    }

    /**
     * Set dni
     *
     * @param string $dni
     * @return Registro
     */
    public function setDni($dni)
    {
        $this->dni = $dni;

        return $this;
    }

    /**
     * Get dni
     *
     * @return string 
     */
    public function getDni()
    {
        return $this->dni;
    }
}
