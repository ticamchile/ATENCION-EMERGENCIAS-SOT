<?php

namespace BcTic\CamSotBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use BcTic\CamSotBundle\Entity\Precio as Precio;

class GenerarPreciosCommand extends ContainerAwareCommand
{

    protected $em = null;

    protected function configure()
    {
        $this
            ->setName('ssee-sot:generar-precios')
            ->setDescription('GENERAR ARCHIVO DE PRECIOS SOT');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->em = $this->getContainer()->get('doctrine')->getManager();  
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);          

        $output->writeln("COMENZANDO PROCESO DE PRECIOS");

        $entities = $this->em->getRepository('BcTicCamSotBundle:Precio')->findBy(
          array('status' => 'PENDING'),
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

  private function process(Precio $precio,InputInterface $input, OutputInterface $output) {
    $output->writeln("   * PROCESANDO: ".$precio->getPath());
    //$precio->setStatus('PROCESSING');
    //$this->em->persist($precio);
    //$this->em->flush();

    //Existe el archivo:
    $file = $precio->getUploadRootDir().$precio->getPath();
    if (!is_readable($file)) throw new \Exception("ARCHIVO ".$precio->getPath()." NO SE ENCUENTRA.");
    if (($handle = fopen($file, "r")) == false) throw new \Exception("ARCHIVO NO SE PUEDE PROCESAR."); 

     //Itero y creo un objeto por cada tupla - si hay no conformidades se registra en la entidad correspondiente.
     $row = 0;
     $hits = 0;
     $hasErrors = false;

     while (($data = fgetcsv($handle, 0,$precio->getColumnsSeparator() )) !== false) {

        try {

          if ($row > 0) {

            $items = $this->rowToObject($data,$input,$output,$precio);

            foreach ($items as $item) { 
             $sql = "INSERT INTO precio_item (precio_id, MARCA_RECURSO_INSUFICIENTE,CUADRILLA_DESASIGNADA,COD_MOVIL,TIPO_MOVIL,ESTADO,ESTADO_DE_FINALIZACION,CODIGO_AMBITO,CODIGO_ELEMENTO_RESPONSABLE,CODIGO_CONDICION,BAREMOS,PRECIO, fecha_desde, fecha_hasta) VALUES (".$precio->getId().",'".$item['MARCA_RECURSO_INSUFICIENTE']."','".$item['CUADRILLA_DESASIGNADA']."','".$item['COD_MOVIL']."','".$item['TIPO_MOVIL']."','".$item['ESTADO']."','".$item['ESTADO_DE_FINALIZACION']."','".$item['CODIGO_AMBITO']."','".$item['CODIGO_ELEMENTO_RESPONSABLE']."','".$item['CODIGO_CONDICION']."','".$item['BAREMOS']."','".$item['PRECIO']."','".$precio->getFechaDesde()->format('Y-m-d')."','".$precio->getFechaHasta()->format('Y-m-d')."')";
             $this->em->getConnection()->exec($sql);
             $this->em->flush();
             }
           
           $items = null;
           
           $hits++;

         } else {
           //DO NOTHING YET WITH THE HEADERS ...
         }

       } catch (\Doctrine\DBAL\DBALException $e) {
         $hasErrors = true;
         $precio->setStatus("HAS_NO_CONFORMITIES");
         $msg = " * ERROR DUPLICACION: TUPLA #".( $row + 1 ).' - '.$e->getMessage().chr(10);
         $output->writeln("  * ".$msg);
         $precio->setNotes($precio->getNotes().'<dl><dd>'.$msg.'</dd></dl>');
         $this->em->persist($precio);
         $this->em->flush();

         //BORRO TODOS LOS PRECIOS CREADOS:
         $sql = "DELETE FROM precio_item WHERE precio_id = ".$precio->getId().";";
         $this->em->getConnection()->exec($sql);
         $this->em->flush();    
         return; //DEVUELVO NO CONTINUA LA EJECUCION.
       }
       $row++;  
     }

     //Menos 1 por la cabecera y porque parte de 0.
     $output->writeln("      > ".($row - 1)." PRECIOS CREADOS ");

    //Si está todo OK, entonces cambio es estado del objeto a OK.
    if ($hasErrors) { 
       //DO NOTHING
    } else {
       $sql = "UPDATE Precio SET status = 'OK' WHERE id = ".$precio->getId()." LIMIT 1;";
       $this->em->getConnection()->exec($sql);
       $this->em->flush();
    }
  }  

  private function rowToObject($data =  array(),InputInterface $input, OutputInterface $output, Precio $precio) {

    $precios = array();

    //El Precio tiene un campo llamado moviles, que debe parsearse como
    $columnas = $precio->getColumnas();

    foreach ($data as $nemo => $value) { 
      if (preg_match('/^IGNORAR_[0-9]/',$columnas[$nemo]) !== 0) continue;

      //EXCEPCIONES:
      switch($columnas[$nemo]) {
        case 'ESTADO_DE_FINALIZACION':
          if ($value === 'P') $value = 'PENDIENTE';
          if ($value === 'T') $value = 'TERMINADO';
          break;
        case 'PRECIO':
          $value = str_replace(array('$','.',' '), array('','',''), $value);
          break;  
      }

      $precios[$columnas[$nemo]] = $value;
    }  

    //LAS COLUMNAS ESPECIALES
    if (isset($precios['MARCA_RECURSO_INSUFICIENTE']) === false) {
      $precios['MARCA_RECURSO_INSUFICIENTE'] = 'NO';
    }

    if (isset($precios['CUADRILLA_DESASIGNADA']) === false) {
      $precios['CUADRILLA_DESASIGNADA'] = 'NO';
    }

    if (isset($precios['ESTADO']) === false) {
      $precios['ESTADO'] = 'CERRADO';
    }

    $preciosResults = array();
    //PREGUNTO LOS MOVILES
    $moviles = isset($precios['COD_MOVIL']) ? $precios['COD_MOVIL'] : $precio->getMoviles();
    foreach(explode(',',$moviles) as $movil) {
      $precios['COD_MOVIL'] = $movil;
      $preciosResults[] = $precios;
    }

    $preciosOutput = array();
    //PREGUNTO LOS MOVILES
    $tipoMoviles = isset($precios['TIPO_MOVIL']) ? $precios['TIPO_MOVIL'] : $precio->getTipoMoviles();    
    foreach($preciosResults as $precioResult) {
      foreach(explode(',',$tipoMoviles) as $tipoMovil) {
        $precioResult['TIPO_MOVIL'] = $tipoMovil;
        $preciosOutput[] = $precioResult;
      }
    }
     
    return $preciosOutput;
  } 
    
  protected function myimplode($thearray){
    return ("'".implode("','", $thearray)."'"); 
  }

  protected function printMemoryUsage($output)
    {
        $output->writeln(sprintf('  >>> Memory usage (currently) %dKB/ (max) %dKB', round(memory_get_usage(true) / 1024), memory_get_peak_usage(true) / 1024));
    }
  
}