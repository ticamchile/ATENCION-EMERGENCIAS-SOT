<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArchivoDeEvento
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ArchivoDeEvento
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
     * @ORM\Column(name="status", type="string", length=25)
     */
    private $status = 'PENDING';

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=25)
     */
    private $tipo = 'EVENTOS'; 

    /**
     * @var string
     *
     * @ORM\Column(name="columns_separator", options={ "defaults": ";"}, type="string", length=1)
     */
    private $columnsSeparator = ';'; 

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
     * @Assert\NotBlank() 
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ArchivoDeEvento
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return ArchivoDeEvento
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
     * Set notes
     *
     * @param string $notes
     * @return ArchivoDeEvento
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
      // target filename to move to
      $this->getFile()->move(
        $this->getUploadRootDir(),
        $this->getCreatedAt().'-'.$this->getFile()->getClientOriginalName()
      );

      // set the path property to the filename where you've saved the file
      $this->path = $this->getFile()->getClientOriginalName();

      // clean up the file property as you won't need it anymore
      $this->file = null;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return ArchivoDeEvento
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
     * Set hash
     *
     * @param string $hash
     * @return ArchivoDeEvento
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return ArchivoDeEvento
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    public function __toString(){
        return $this->path;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $date = new \DateTime();
        $this->createdAt = $date->format('U');
    }

    /**
     * Set columnsSeparator
     *
     * @param string $columnsSeparator
     * @return ArchivoDeEvento
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
}
