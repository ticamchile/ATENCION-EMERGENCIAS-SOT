<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Precio
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Precio
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
     * @ORM\Column(name="nombre", type="string", length=150)
     */
    private $nombre;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="descripcion", type="text")
     */
    private $descripcion;

   /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="moviles", type="text")
     */
    private $moviles = '*';  

   /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="tipo_moviles", type="text")
     */
    private $tipoMoviles = '*';  

    /**
     * @var array
     * @ORM\Column(name="columnas", type="array")
     */
    private $columnas = array(
        'BAREMOS',
        'PRECIO',
        'CODIGO_ELEMENTO_RESPONSABLE',
        'IGNORAR_0',
        'CODIGO_AMBITO',
        'IGNORAR_1',
        'CODIGO_CONDICION',
        'IGNORAR_2',
        'ESTADO_DE_FINALIZACION',
        'IGNORAR_3',
        'ESTADO',
        'MARCA_RECURSO_INSUFICIENTE',
        'CUADRILLA_DESASIGNADA',
        'COD_MOVIL',
        'TIPO_MOVIL'
        );      

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="status", type="string", length=25)
     */
    private $status = "PENDING";

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @ORM\Column(name="fecha_desde", type="date")
     */
    private $fecha_desde;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @ORM\Column(name="fecha_hasta", type="date")
     */
    private $fecha_hasta;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

     /**
     * @Assert\File(maxSize="50000000", mimeTypes = {"text/plain"}, mimeTypesMessage = "Debe importar un archivo en formato de texto plano")
     * @Assert\NotBlank()     
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(name="columns_separator", options={ "defaults": ";"}, type="string", length=1)
     */
    private $columnsSeparator = ';';  

    /**
     * @var string
     *
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;       


    public function __construct(){
        $this->fecha_desde = new \DateTime(date('Y').'-01-01');
        $this->fecha_hasta = new \DateTime(date('Y').'-12-31');
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
     * Set nombre
     *
     * @param string $nombre
     * @return Precio
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
     * @return Precio
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
     * @return Precio
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
     * Set monto
     *
     * @param float $monto
     * @return Precio
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;

        return $this;
    }

    /**
     * Get monto
     *
     * @return float 
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * Set fecha_desde
     *
     * @param \Date$fechaDesde
     * @return Precio
     */
    public function setFechaDesde($fechaDesde)
    {
        $this->fecha_desde = $fechaDesde;

        return $this;
    }

    /**
     * Get fecha_desde
     *
     * @return \Date
     */
    public function getFechaDesde()
    {
        return $this->fecha_desde;
    }

    /**
     * Set fecha_hasta
     *
     * @param \Date $fechaHasta
     * @return Precio
     */
    public function setFechaHasta($fechaHasta)
    {
        $this->fecha_hasta = $fechaHasta;

        return $this;
    }

    /**
     * Get fecha_hasta
     *
     * @return \Date
     */
    public function getFechaHasta()
    {
        return $this->fecha_hasta;
    }

        public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/';
    }

    public function upload()
    {
    // the file property can be empty if the field is not required
      if (null === $this->getFile()) {
        return;
      }

      // use the original file name here but you should
      // sanitize it at least to avoid any security issues

      // move takes the target directory and then the
      $suffix = date('U');
      // target filename to move to
      $this->getFile()->move(
        $this->getUploadRootDir(),
        $suffix.'-'.$this->getFile()->getClientOriginalName()
      );

      // set the path property to the filename where you've saved the file
      $this->path = $suffix.'-'.$this->getFile()->getClientOriginalName();

      // clean up the file property as you won't need it anymore
      $this->file = null;
    }


    /**
     * Set path
     *
     * @param string $path
     * @return Precio
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

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    } 

    /**
     * Set columnsSeparator
     *
     * @param string $columnsSeparator
     * @return Precio
     */
    public function setColumnsSeparator($columnsSeparator)
    {
        $this->columnsSeparator = $columnsSeparator;

        return $this;
    }

    /**
     * Get columnsSeparator
     *
     * @return string 
     */
    public function getColumnsSeparator()
    {
        return $this->columnsSeparator;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Precio
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set moviles
     *
     * @param string $moviles
     * @return Precio
     */
    public function setMoviles($moviles)
    {
        $this->moviles = $moviles;

        return $this;
    }

    /**
     * Get moviles
     *
     * @return string 
     */
    public function getMoviles()
    {
        return $this->moviles;
    }

    /**
     * Set columnas
     *
     * @param array $columnas
     * @return Precio
     */
    public function setColumnas($columnas)
    {
        $this->columnas = $columnas;

        return $this;
    }

    /**
     * Get columnas
     *
     * @return array 
     */
    public function getColumnas()
    {
        return $this->columnas;
    }

    /**
     * Set tipoMoviles
     *
     * @param string $tipoMoviles
     * @return Precio
     */
    public function setTipoMoviles($tipoMoviles)
    {
        $this->tipoMoviles = $tipoMoviles;

        return $this;
    }

    /**
     * Get tipoMoviles
     *
     * @return string 
     */
    public function getTipoMoviles()
    {
        return $this->tipoMoviles;
    }
}
