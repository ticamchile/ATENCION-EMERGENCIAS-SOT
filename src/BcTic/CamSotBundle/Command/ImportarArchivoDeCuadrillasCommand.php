<?php

namespace BcTic\CamSotBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use BcTic\CamSotBundle\Entity\Registro as Registro;
use BcTic\CamSotBundle\Entity\ArchivoDeEvento as ArchivoDeEvento;
use BcTic\CamSotBundle\Entity\FuenteDeEvento as FuenteDeEvento;
use BcTic\CamSotBundle\Entity\CausaDeEvento as CausaDeEvento;
use BcTic\CamSotBundle\Entity\PropiedadDeEvento as PropiedadDeEvento;
use BcTic\CamSotBundle\Entity\CuadrillaDeEvento as CuadrillaDeEvento;
use BcTic\CamSotBundle\Entity\PropiedadDeCuadrillaDeEvento as PropiedadDeCuadrillaDeEvento;
use BcTic\CamSotBundle\Entity\Evento as Evento;
use BcTic\CamSotBundle\Entity\EventoArchivoDeEvento as EventoArchivoDeEvento;

class ImportarArchivoDeCuadrillasCommand extends ContainerAwareCommand
{

    protected $em = null;

    protected function configure()
    {
        $this
            ->setName('ssee-sot:importar-archivo-de-cuadrillas')
            ->setDescription('IMPORTAR ARCHIVO DE CUADRILLAS SOT');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->em = $this->getContainer()->get('doctrine')->getManager();  
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);          

        $output->writeln("COMENZANDO PROCESO DE CUADRILLAS");

        $entities = $this->em->getRepository('BcTicCamSotBundle:ArchivoDeEvento')->findBy(
          array('status' => 'PENDING', 'tipo' => 'CUADRILLAS'),
          array(),
          1,
          0
          );

        foreach ($entities as $entity) {
          try {
            $this->process($entity, $input, $output);  
          } catch (\Exception $e) {
            $output->writeln("  ERROR: ".$e->getMessage());
          }  
        }
        
        $output->writeln("EJECUCION FINALIZADA. Good Bye!");
    }

  private function process(ArchivoDeEvento $archivoDeEvento,InputInterface $input, OutputInterface $output) {
    $output->writeln("   * PROCESANDO: ".$archivoDeEvento->getPath());
    $archivoDeEvento->setStatus('PROCESSING');
    $this->em->persist($archivoDeEvento);
    $this->em->flush();

    //Existe el archivo:
    $file = $archivoDeEvento->getUploadRootDir().$archivoDeEvento->getCreatedAt().'-'.$archivoDeEvento->getPath();
    if (!is_readable($file)) throw new \Exception("ARCHIVO ".$archivoDeEvento->getCreatedAt().'-'.$archivoDeEvento->getPath()." NO SE ENCUENTRA.");
    if (($handle = fopen($file, "r")) == false) throw new \Exception("ARCHIVO NO SE PUEDE PROCESAR."); 

     //Itero y creo un objeto por cada tupla - si hay no conformidades se registra en la entidad correspondiente.
     $row = 0;
     $hits = 0;
     while (($data = fgetcsv($handle, 0, $archivoDeEvento->getColumnsSeparator())) !== false) {
        //36 es el formato
        if (count($data) != 27) { 
          $archivoDeEvento->setStatus("ERROR");
          $msg = "ARCHIVO LINEA ".$row." NO TIENE 27 COLUMNAS, TIENE ".count($data).". NO SE PUEDE PROCESAR EL ARCHIVO Y SE MARCA COMO NULO.";
          $archivoDeEvento->setNotes($archivoDeEvento->getNotes().' * '.$msg);
          $this->em->persist($archivoDeEvento);
          $this->em->flush();
          throw new \Exception($msg); 
        }

       try {

           //BORRO LOS EVENTOS, MOVILES Y PROMESAS QUE TIENE LA BD
           $sql = "DELETE FROM CuadrillaDeEvento WHERE evento_id IN (SELECT id FROM Evento WHERE nombre = '".$data[0]."') AND CuadrillaDeEvento.valor = '".$data[3]."'";
           $this->em->getConnection()->exec($sql);
           $this->em->flush();

       } catch (\Doctrine\DBAL\DBALException $e) {
         $output->writeln("  ERROR PERSISTENCIA: ".$e->getMessage());
         $hits--;
       }
       $row++;  
     }

     //Itero y creo un objeto por cada tupla - si hay no conformidades se registra en la entidad correspondiente.
     $row = 0;
     $hits = 0;
     if (($handle = fopen($file, "r")) == false) throw new \Exception("ARCHIVO NO SE PUEDE PROCESAR."); 
     while (($data = fgetcsv($handle, 0, $archivoDeEvento->getColumnsSeparator())) !== false) {
        //36 es el formato
        if (count($data) != 27) { 
          $archivoDeEvento->setStatus("ERROR");
          $msg = "ARCHIVO LINEA ".$row." NO TIENE 27 COLUMNAS, TIENE ".count($data).". NO SE PUEDE PROCESAR EL ARCHIVO Y SE MARCA COMO NULO.";
          $archivoDeEvento->setNotes($archivoDeEvento->getNotes().' * '.$msg);
          $this->em->persist($archivoDeEvento);
          $this->em->flush();
          throw new \Exception($msg); 
        }

       try {
         if ($row > 0) {

           $evento = $this->rowToObject($data,$input,$output);
           $this->em->flush();

           //Hago un insert Manual -- 
           $sql = "INSERT INTO EventoArchivoDeEvento (evento_id, archivo_de_evento_id) VALUES (".$evento->getId().",".$archivoDeEvento->getId().")";
           $this->em->getConnection()->exec($sql);

           $evento = null;
           
           $hits++;
           if ($hits == 50000) {
             $output->writeln("      > ".$hits." CUADRILLAS PERSISTIDOS ");
             return;
           }
         } else {
           //DO NOTHING YET WITH THE HEADERS ...
         }
       } catch (\Doctrine\DBAL\DBALException $e) {
         $output->writeln("  ERROR PERSISTENCIA: ".$e->getMessage());
         $hits--;
       }
       $row++;  
     }

     //Menos 1 por la cabecera y porque parte de 0.
     $output->writeln("      > ".($row - 1)." CUADRILLAS PERSISTIDOS ");

     //Si está todo OK, entonces cambio es estado del objeto a OK.
     $archivoDeEvento->setStatus("OK");
     $sql = "UPDATE ArchivoDeEvento SET status = 'OK' WHERE id = ".$archivoDeEvento->getId()." LIMIT 1;";
     $this->em->getConnection()->exec($sql);
     $this->em->flush();

  }  

  private function rowToObject($data =  array(),InputInterface $input, OutputInterface $output) {

    //Como precaución chequeo si está abierto el EM:
    $this->getContainer()->get('doctrine')->resetManager();
    $this->em = $this->getContainer()->get('doctrine')->getManager();  
    
    //Existe el objeto.
    $evento = $this->em->getRepository('BcTicCamSotBundle:Evento')->findOneBy(array('nombre' => $data[0]));

    if (is_object($evento)) {

    } else {
      throw new \Doctrine\DBAL\DBALException($data[0]." NO EXISTE.");
    }

    //Lo primero es que creo un objeto con el nemo = 3 que es el movil.
    $cuadrillaDeEvento = new CuadrillaDeEvento();
    $cuadrillaDeEvento->setEvento($evento);

    foreach ($data as $nemo => $value) {

      if ($nemo == 0) continue; //0 es el nombre
      if ($nemo == 3) {
        $cuadrillaDeEvento->setValor($value);
      } else {
        //Si está vacía la propiedad, no importa por que debe hacer el "espacio para la edición".
        $propiedad = new PropiedadDeCuadrillaDeEvento();
        $propiedad->setNemo(utf8_encode($nemo));
        $propiedad->setValor(utf8_encode($value));
        $propiedad->setCuadrillaDeEvento($cuadrillaDeEvento);
        $this->em->persist($propiedad);
      }  
    }

    $this->em->persist($cuadrillaDeEvento);
    $this->em->flush();
    $cuadrillaDeEvento = null;

    gc_collect_cycles();

    return $evento;

  }
    
  protected function myimplode($thearray){
    return ("'".implode("','", $thearray)."'"); 
  }

  protected function printMemoryUsage($output)
    {
        $output->writeln(sprintf('  >>> Memory usage (currently) %dKB/ (max) %dKB', round(memory_get_usage(true) / 1024), memory_get_peak_usage(true) / 1024));
    }
  
}