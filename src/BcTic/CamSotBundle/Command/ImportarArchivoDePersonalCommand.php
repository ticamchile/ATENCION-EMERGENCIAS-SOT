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
use BcTic\CamSotBundle\Entity\Evento as Evento;
use BcTic\CamSotBundle\Entity\EventoArchivoDeEvento as EventoArchivoDeEvento;

class ImportarArchivoDePersonalCommand extends ContainerAwareCommand
{

    protected $em = null;

    protected function configure()
    {
        $this
            ->setName('ssee-sot:importar-archivo-de-personal')
            ->setDescription('IMPORTAR ARCHIVO DE PERSONAL SOT');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->em = $this->getContainer()->get('doctrine')->getManager();  
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);    

        $output->writeln("COMENZANDO PROCESO DE PERSONAL");

        $entities = $this->em->getRepository('BcTicCamSotBundle:ArchivoDeEvento')->findBy(
          array('status' => 'PENDING', 'tipo' => 'PERSONAL'),
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
        if (count($data) != 5) { 
          $archivoDeEvento->setStatus("ERROR");
          $msg = "ARCHIVO LINEA ".$row." NO TIENE 5 COLUMNAS, TIENE ".count($data).". NO SE PUEDE PROCESAR EL ARCHIVO Y SE MARCA COMO NULO.";
          $archivoDeEvento->setNotes($archivoDeEvento->getNotes().' * '.$msg);
          $this->em->persist($archivoDeEvento);
          $this->em->flush();
          throw new \Exception($msg); 
        }

       try {
         if ($row > 0) {

           $this->rowToObject($data,$input,$output);
           $this->em->flush();
           
           $hits++;
           if ($hits == 50000) {
             $output->writeln("      > ".$hits." PERSONAL PERSISTIDO");
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
     $output->writeln("      > ".($row - 1)." PERSONAL/REGISTRO PERSISTIDOS ");

     //Si está todo OK, entonces cambio es estado del objeto a OK.
     $archivoDeEvento->setStatus("OK");
     $sql = "UPDATE ArchivoDeEvento SET status = 'OK' WHERE id = ".$archivoDeEvento->getId()." LIMIT 1;";
     $this->em->getConnection()->exec($sql);
     

  }  

  private function rowToObject($data =  array(),InputInterface $input, OutputInterface $output) {

    //Como precaución chequeo si está abierto el EM:
    $this->getContainer()->get('doctrine')->resetManager();
    $this->em = $this->getContainer()->get('doctrine')->getManager();  
    
    if ( (strlen(utf8_encode($data[2])) > 4 ) and (strlen(utf8_encode($data[4])) > 0 ) ) {
     if (count($this->em->getRepository('BcTicCamSotBundle:Registro')->findBy(array('nemo' => utf8_encode($data[4])))) == 0) {
       //Creo un nuevo registro:
       $registro = new Registro();
       $registro->setNemo(utf8_encode($data[4]));
       $registro->setNombre(utf8_encode($data[2]));
       $registro->setDni(utf8_encode($data[1]));
       $this->em->persist($registro);
     } 
    }

    gc_collect_cycles();

  }
    
  protected function myimplode($thearray){
    return ("'".implode("','", $thearray)."'"); 
  }

  protected function printMemoryUsage($output)
    {
        $output->writeln(sprintf('  >>> Memory usage (currently) %dKB/ (max) %dKB', round(memory_get_usage(true) / 1024), memory_get_peak_usage(true) / 1024));
    }
  
}