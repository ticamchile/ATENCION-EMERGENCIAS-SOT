<?php

namespace BcTic\CamSotBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;


/**
 * Cierre
 *
 */
class Cierre
{
    
    private $archivoDeCierre;
    
    public function setArchivoDeCierre($archivoDeCierre)
    {
        $this->archivoDeCierre = $archivoDeCierre;

        return $this;
    }

    public function getArchivoDeCierre()
    {
        return $this->archivoDeCierre;
    }

}
