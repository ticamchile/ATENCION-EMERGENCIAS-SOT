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
use BcTic\CamSotBundle\Entity\ArchivoDeCierre as ArchivoDeCierre;
use BcTic\CamSotBundle\Entity\FuenteDeEvento as FuenteDeEvento;
use BcTic\CamSotBundle\Entity\CausaDeEvento as CausaDeEvento;
use BcTic\CamSotBundle\Entity\PropiedadDeEvento as PropiedadDeEvento;
use BcTic\CamSotBundle\Entity\Evento as Evento;
use BcTic\CamSotBundle\Entity\Historial as Historial;
use BcTic\CamSotBundle\Entity\EventoArchivoDeEvento as EventoArchivoDeEvento;

class GenerarArchivoDeCierreCommand extends ContainerAwareCommand
{

    protected $em = null;

    protected function configure()
    {
        $this
            ->setName('ssee-sot:generar-archivo-de-cierre')
            ->setDescription('GENERAR ARCHIVO DE CIERRE SOT');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->em = $this->getContainer()->get('doctrine')->getManager();  
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);          

        $output->writeln("COMENZANDO PROCESO DE CIERRE");

        $entities = $this->em->getRepository('BcTicCamSotBundle:ArchivoDeCierre')->findBy(
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

  private function process(ArchivoDeCierre $archivoDeCierre, InputInterface $input, OutputInterface $output) {
    $output->writeln("   * PROCESANDO: ID ".$archivoDeCierre->getId());
    $archivoDeCierre->setStatus('PROCESSING');
    $this->em->persist($archivoDeCierre);
    $this->em->flush();

    $file = $this->getContainer()->get('kernel')->getRootDir().'/Resources/data/cierres/cierre-'.$archivoDeCierre->getFechaDeInicio()->format('Y-m-d').'-'.$archivoDeCierre->getFechaDeTermino()->format('Y-m-d').'-'.$archivoDeCierre->getId().'-data.csv';
    
    if (file_exists($file)) unlink($file);
    file_put_contents($file, "");            
    if(!is_writable($file)) throw new \Exception("FILE ".$file." NOT WRITEABLE.");
  
    //Escribo la cabecera:
    $fp = fopen($file, 'w');
    $cabecera = array("EVENTO","MARCA RECURSO INSUFICIENTE","CUADRILLA DESASIGNADA","COD MOVIL","TIPO MOVIL","EMPRESA","SUPERVISOR","FECHA DESPACHO","FECHA ACEPTACION","FECHA RUTA","FECHA LIBERACION","ESTADO","TIPO EVENTO","COMUNA","INICIO","ARRIBADO","FIN","PDA FECHA CONTACTO ESTIMADO","PDA FECHA TERMINO ESTIMADO","PDA FECHA CONTACTO REAL","PDA FECHA TERMINO REAL","ESTADO DE FINALIZACION","OBSERVACION CUMPLIMENTACION","COD MOVIL","TIPO MOVIL","ESTADO","LLEGADA EVENTO","ASIGNADA","ACEPTADA","EN RUTA","ARRIBO","LIBERADA","CUMPL.PROMESA","CODIGO AMBITO","CODIGO ELEMENTO RESPONSABLE","CODIGO CONDICION","DESCRIPCION AMBITO","DESCRIPCION ELEMENTO RESPONSABLE","DESCRIPCION CONDICION");
    fputcsv($fp, $cabecera,';','"');
    fclose($fp);

    //BORRO TODOS LOS EVENTOS QUE NO SEAN "B";
    $sql =  "DELETE FROM Evento WHERE nombre NOT REGEXP '[B].';";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
        
    //Recorro todos los eventos con fecha de Inicio entre los valores del ArchivoDeCierre object:
    $entitiesEvento = $this->em->getRepository('BcTicCamSotBundle:PropiedadDeEvento')
                      ->createQueryBuilder('p')
                      ->select('DISTINCT e.nombre')
                      ->leftJoin('p.evento','e')
                      ->where('p.nemo = 8 AND ( 
                                                MySQLStrToDate(p.valor,\'%d-%m-%Y %h:%i:%s\',\'%Y-%m-%d\') >= ?1
                                          AND 
                                                MySQLStrToDate(p.valor,\'%d-%m-%Y %h:%i:%s\',\'%Y-%m-%d\') <= ?2
                                               )')
                      ->orderBy('e.nombre', 'ASC')
                      ->getQuery();

    //AHORA QUE TENGO LOS CODIGOS DE EVENTO - BUSCO LAS CUADRILLAS ASIGNADAS A DICHOS EVENTOS.

    $stmt = $this->em->getConnection()->prepare($entitiesEvento->getSql());
    $stmt->bindValue(1, $archivoDeCierre->getFechaDeInicio()->format('Y-m-d')); 
    $stmt->bindValue(2, $archivoDeCierre->getFechaDeTermino()->format('Y-m-d')); 
    $stmt->execute();
    $fetch = $stmt->fetchAll();
    $eventos = $this->array_column($fetch,'nombre0');                    
    //Ahora tomo los eventos y vacío al archivo: 
    $noConformidad = false;                  
    $fp = fopen($file, 'a+');
    foreach ($eventos as $key => $evento) {
      //Busco la cuadrilla que tenga este Evento:
      $cuadrillasDeEvento = $this->em->getRepository('BcTicCamSotBundle:CuadrillaDeEvento')
                                     ->createQueryBuilder('c')
                                     ->leftJoin('c.evento','e')
                                     ->where('e.nombre = :valor')
                                     ->setParameters(
                                        array(
                                          'valor' => $evento,
                                          ))
                                     ->getQuery()
                                     ->getResult();

      foreach($cuadrillasDeEvento as $entity) {
         $this->generateCsvRow($fp,$entity,$archivoDeCierre);
      }                               

    } 
    fclose($fp);               

    //Tiene NO CONFORMIDADES?
    if ($noConformidad) {
      //$archivoDeCierre->setStatus('HAS_NO_CONFORMITIES');
     $output->writeln("   * PROCESADO: ARCHIVO CON NO CONFORMIDADES");
    } 
      
    $archivoDeCierre->setPath(str_replace($this->getContainer()->get('kernel')->getRootDir().'/Resources/data/','',$file));
    $archivoDeCierre->setStatus('DONE');
    $output->writeln("   * PROCESADO: ARCHIVO ".$file);

    $this->em->persist($archivoDeCierre); 

    $this->em->flush();
    gc_collect_cycles();

  }

  private function generateCsvRow(&$fp,$entity,$archivoDeCierre) {

    try {

        $fechaDespacho = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPropiedadCuadrillaDeEvento(12)->getValor());
        if (!is_object($fechaDespacho)) {
          $fechaDespacho = "";
        } else {
          $fechaDespacho= $fechaDespacho->format("d-m-Y h:i");
        } 

        $fechaAceptacion = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPropiedadCuadrillaDeEvento(15)->getValor());
        if (!is_object($fechaAceptacion)) {
          $fechaAceptacion = "";
        } else {
          $fechaAceptacion = $fechaAceptacion->format("d-m-Y h:i");
        } 

        $fechaRuta = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPropiedadCuadrillaDeEvento(18)->getValor());
        if (!is_object($fechaRuta)) {
          $fechaRuta = "";
        } else {
          $fechaRuta = $fechaRuta->format("d-m-Y h:i");
        }    

        $fechaLiberacion = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPropiedadCuadrillaDeEvento(24)->getValor());
        if (!is_object($fechaLiberacion)) {
          $fechaLiberacion = "";
        } else {
          $fechaLiberacion = $fechaLiberacion->format("d-m-Y h:i");
        }    

        //20-09-2014 1:19
        $fechaInicio = \DateTime::createFromFormat("d-m-Y h:i",$entity->getEvento()->getPropiedadDeEvento(8)->getValor());
        if (!is_object($fechaInicio)) {
          $fechaInicio = "";
        } else {
          $fechaInicio = $fechaInicio->format("d-m-Y h:i");
        }  

        //20-09-2014 1:19
        $fechaArribado = \DateTime::createFromFormat("d-m-Y h:i",$entity->getEvento()->getPropiedadDeEvento(9)->getValor());
        if (!is_object($fechaArribado)) {
          $fechaArribado = "";
        } else {
          $fechaArribado = $fechaArribado->format("d-m-Y h:i");
        }  

        //20-09-2014 1:19
        $fechaFin = \DateTime::createFromFormat("d-m-Y h:i",$entity->getEvento()->getPropiedadDeEvento(10)->getValor());
        if (!is_object($fechaFin)) {
          $fechaFin = "";
        } else {
          $fechaFin = $fechaFin->format("d-m-Y h:i");
        }  

        //20-09-2014 1:19
        $fechaPDAFechaContactoEstimado = \DateTime::createFromFormat("d-m-Y h:i",$entity->getEvento()->getPropiedadDeEvento(26)->getValor());
        if (!is_object($fechaPDAFechaContactoEstimado)) {
          $fechaPDAFechaContactoEstimado = "";
        } else {
          $fechaPDAFechaContactoEstimado = $fechaPDAFechaContactoEstimado->format("d-m-Y h:i");
        }  

        $fechaPDAFechaTerminoEstimado = \DateTime::createFromFormat("d-m-Y h:i",$entity->getEvento()->getPropiedadDeEvento(27)->getValor());
        if (!is_object($fechaPDAFechaTerminoEstimado)) {
          $fechaPDAFechaTerminoEstimado = "";
        } else {
          $fechaPDAFechaTerminoEstimado = $fechaPDAFechaTerminoEstimado->format("d-m-Y h:i");
        }  

        //20-09-2014 1:19
        $fechaPDAFechaContactoReal = \DateTime::createFromFormat("d-m-Y h:i",$entity->getEvento()->getPropiedadDeEvento(29)->getValor());
        if (!is_object($fechaPDAFechaContactoReal)) {
          $fechaPDAFechaContactoReal = "";
        } else {
          $fechaPDAFechaContactoReal = $fechaPDAFechaContactoReal->format("d-m-Y h:i");
        }        

        //20-09-2014 1:19
        $fechaPDAFechaTerminoReal = \DateTime::createFromFormat("d-m-Y h:i",$entity->getEvento()->getPropiedadDeEvento(30)->getValor());
        if (!is_object($fechaPDAFechaTerminoReal)) {
          $fechaPDAFechaTerminoReal = "";
        } else {
          $fechaPDAFechaTerminoReal = $fechaPDAFechaTerminoReal->format("d-m-Y h:i");
        }  

        $fechaLlegadaEvento = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPromesaDeEvento(8)->getValor());
        if (!is_object($fechaLlegadaEvento)) {
          $fechaLlegadaEvento= "";
        } else {
          $fechaLlegadaEvento = $fechaLlegadaEvento->format("d-m-Y h:i");
        }                                             

        $fechaAsignacionEvento = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPromesaDeEvento(9)->getValor());
        if (!is_object($fechaAsignacionEvento)) {
          $fechaAsignacionEvento = "";
        } else {
          $fechaAsignacionEvento = $fechaAsignacionEvento->format("d-m-Y h:i");
        }    

        $fechaAceptacionEvento = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPromesaDeEvento(10)->getValor());
        if (!is_object($fechaAceptacionEvento)) {
          $fechaAceptacionEvento = "";
        } else {
          $fechaAceptacionEvento = $fechaAceptacionEvento->format("d-m-Y h:i");
        }        

        $fechaEnRutaEvento = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPromesaDeEvento(11)->getValor());
        if (!is_object($fechaEnRutaEvento)) {
          $fechaEnRutaEvento = "";
        } else {
          $fechaEnRutaEvento = $fechaEnRutaEvento->format("d-m-Y h:i");
        }   

        $fechaArriboEvento = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPromesaDeEvento(12)->getValor());
        if (!is_object($fechaArriboEvento)) {
          $fechaArriboEvento= "";
        } else {
          $fechaArriboEvento = $fechaArriboEvento->format("d-m-Y h:i");
        }  

        $fechaLiberadaEvento = \DateTime::createFromFormat("d-m-Y h:i",$entity->getPromesaDeEvento(13)->getValor());
        if (!is_object($fechaLiberadaEvento)) {
          $fechaLiberadaEvento= "";
        } else {
          $fechaLiberadaEvento = $fechaLiberadaEvento->format("d-m-Y h:i");
        }                    

        fputcsv($fp, array(
            $entity->getEvento()->getNombre(),
            $entity->getPropiedadCuadrillaDeEvento(1)->getValor(),
            $entity->getPropiedadCuadrillaDeEvento(2)->getValor(),
            $entity->getValor(),
            $entity->getPropiedadCuadrillaDeEvento(4)->getValor(),
            $entity->getPropiedadCuadrillaDeEvento(5)->getValor(),
            $entity->getPropiedadCuadrillaDeEvento(7)->getValor(),
            $fechaDespacho,
            $fechaAceptacion,
            $fechaRuta,
            $fechaLiberacion,
            $entity->getEvento()->getPropiedadDeEvento(1)->getValor(),
            $entity->getEvento()->getPropiedadDeEvento(2)->getValor(),
            $entity->getEvento()->getPropiedadDeEvento(7)->getValor(),
            $fechaInicio,
            $fechaArribado,
            $fechaFin,
            $fechaPDAFechaContactoEstimado,
            $fechaPDAFechaTerminoEstimado,
            $fechaPDAFechaContactoReal,
            $fechaPDAFechaTerminoReal,
            $entity->getEvento()->getPropiedadDeEvento(31)->getValor(),
            $entity->getEvento()->getPropiedadDeEvento(35)->getValor(),
            $entity->getPromesaDeEvento(1)->getValor(),
            $entity->getPromesaDeEvento(2)->getValor(),
            $entity->getPromesaDeEvento(6)->getValor(),
            $fechaLlegadaEvento,
            $fechaAsignacionEvento,
            $fechaAceptacionEvento,
            $fechaEnRutaEvento,
            $fechaArriboEvento,
            $fechaLiberadaEvento,
            $entity->getPromesaDeEvento(19)->getValor(),
            $entity->getEvento()->getCausaDeEvento(4)->getValor(),
            $entity->getEvento()->getCausaDeEvento(2)->getValor(),
            $entity->getEvento()->getCausaDeEvento(6)->getValor(),
            $entity->getEvento()->getCausaDeEvento(5)->getValor(),
            $entity->getEvento()->getCausaDeEvento(7)->getValor(),
            $entity->getEvento()->getCausaDeEvento(8)->getValor()
          ),';','"');

    $sql = "INSERT INTO cierre (  archivo_de_cierre_id,  EVENTO,  MARCA_RECURSO_INSUFICIENTE,  CUADRILLA_DESASIGNADA,  COD_MOVIL,  TIPO_MOVIL,  EMPRESA,  SUPERVISOR,  FECHA_DESPACHO,  FECHA_ACEPTACION,  FECHA_RUTA,  FECHA_LIBERACION,  ESTADO,  TIPO_EVENTO,  COMUNA,  INICIO,  ARRIBADO,  FIN,  PDA_FECHA_CONTACTO_ESTIMADO,  PDA_FECHA_TERMINO_ESTIMADO,  PDAFECHA_CONTACTO_REAL,  PDA_FECHA_TERMINO_REAL,  ESTADO_DE_FINALIZACION,  OBSERVACION_CUMPLIMENTACION,  LLEGADA_EVENTO,  ASIGNADA,  ACEPTADA,  EN_RUTA,  ARRIBO,  LIBERADA,  CUMPL_PROMESA,  CODIGO_AMBITO,  CODIGO_ELEMENTO_RESPONSABLE,  CODIGO_CONDICION,  DESCRIPCION_AMBITO,  DESCRIPCIONELEMENTO_RESPONSABLE,  DESCRIPCION_CONDICION) VALUES ('".
            $archivoDeCierre->getId()."','".
            $entity->getEvento()->getNombre()."','".
            $entity->getPropiedadCuadrillaDeEvento(1)->getValor()."','".
            $entity->getPropiedadCuadrillaDeEvento(2)->getValor()."','".
            $entity->getValor()."','".
            $entity->getPropiedadCuadrillaDeEvento(4)->getValor()."','".
            $entity->getPropiedadCuadrillaDeEvento(5)->getValor()."','".
            $entity->getPropiedadCuadrillaDeEvento(7)->getValor()."','".
            $fechaDespacho."','".
            $fechaAceptacion."','".
            $fechaRuta."','".
            $fechaLiberacion."','".
            $entity->getEvento()->getPropiedadDeEvento(1)->getValor()."','".
            $entity->getEvento()->getPropiedadDeEvento(2)->getValor()."','".
            $entity->getEvento()->getPropiedadDeEvento(7)->getValor()."','".
            $fechaInicio."','".
            $fechaArribado."','".
            $fechaFin."','".
            $fechaPDAFechaContactoEstimado."','".
            $fechaPDAFechaTerminoEstimado."','".
            $fechaPDAFechaContactoReal."','".
            $fechaPDAFechaTerminoReal."','".
            $entity->getEvento()->getPropiedadDeEvento(31)->getValor()."','".
            $entity->getEvento()->getPropiedadDeEvento(35)->getValor()."','".
            $fechaLlegadaEvento."','".
            $fechaAsignacionEvento."','".
            $fechaAceptacionEvento."','".
            $fechaEnRutaEvento."','".
            $fechaArriboEvento."','".
            $fechaLiberadaEvento."','".
            $entity->getPromesaDeEvento(19)->getValor()."','".
            $entity->getEvento()->getCausaDeEvento(4)->getValor()."','".
            $entity->getEvento()->getCausaDeEvento(2)->getValor()."','".
            $entity->getEvento()->getCausaDeEvento(6)->getValor()."','".
            $entity->getEvento()->getCausaDeEvento(5)->getValor()."','".
            $entity->getEvento()->getCausaDeEvento(7)->getValor()."','".
            $entity->getEvento()->getCausaDeEvento(8)->getValor().
          "')";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();


    } catch (\InvalidArgumentException $e) {
        //Lo creo como NO CONFORMIDAD;
        $output->writeln("   * NO CONFORMIDAD: ".$e->getMessage());
        $historial = new Historial();
        $historial->setNombre("EVENTO ".$entity->getEvento()->getNombre()." CON NO CONFORMIDAD.");
        $historial->setDescripcion($e->getMessage());
        $historial->setEvento($entity->getEvento()); 
        $this->em->persist($historial);
        $noConformidad = true; 
      }

  }
    
  protected function myimplode($thearray){
    return ("'".implode("','", $thearray)."'"); 
  }

  protected function printMemoryUsage($output)
    {
        $output->writeln(sprintf('  >>> Memory usage (currently) %dKB/ (max) %dKB', round(memory_get_usage(true) / 1024), memory_get_peak_usage(true) / 1024));
    }

function array_column($input = null, $columnKey = null, $indexKey = null)
{
// Using func_get_args() in order to check for proper number of
// parameters and trigger errors exactly as the built-in array_column()
// does in PHP 5.5.
$argc = func_num_args();
$params = func_get_args();
if ($argc < 2) {
trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
return null;
}
if (!is_array($params[0])) {
trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
return null;
}
if (!is_int($params[1])
&& !is_float($params[1])
&& !is_string($params[1])
&& $params[1] !== null
&& !(is_object($params[1]) && method_exists($params[1], '__toString'))
) {
trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
return false;
}
if (isset($params[2])
&& !is_int($params[2])
&& !is_float($params[2])
&& !is_string($params[2])
&& !(is_object($params[2]) && method_exists($params[2], '__toString'))
) {
trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
return false;
}
$paramsInput = $params[0];
$paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
$paramsIndexKey = null;
if (isset($params[2])) {
if (is_float($params[2]) || is_int($params[2])) {
$paramsIndexKey = (int) $params[2];
} else {
$paramsIndexKey = (string) $params[2];
}
}
$resultArray = array();
foreach ($paramsInput as $row) {
$key = $value = null;
$keySet = $valueSet = false;
if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
$keySet = true;
$key = (string) $row[$paramsIndexKey];
}
if ($paramsColumnKey === null) {
$valueSet = true;
$value = $row;
} elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
$valueSet = true;
$value = $row[$paramsColumnKey];
}
if ($valueSet) {
if ($keySet) {
$resultArray[$key] = $value;
} else {
$resultArray[] = $value;
}
}
}
return $resultArray;
}    
  
}