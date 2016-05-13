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
use BcTic\CamSotBundle\Entity\Liquidacion as Liquidacion;

class GenerarLiquidacionesCommand extends ContainerAwareCommand
{

    protected $em = null;

    protected function configure()
    {
        $this
            ->setName('ssee-sot:generar-liquidaciones')
            ->setDescription('GENERAR ARCHIVO DE LIQUIDACIONES SOT');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->em = $this->getContainer()->get('doctrine')->getManager();  
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);          

        $output->writeln("COMENZANDO PROCESO DE LIQUIDACIÓN");

        $entities = $this->em->getRepository('BcTicCamSotBundle:Liquidacion')->findBy(
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

  private function process(Liquidacion $liquidacion,InputInterface $input, OutputInterface $output) {
    $output->writeln("   * PROCESANDO: ".$liquidacion->getArchivoDeCierre());
    $liquidacion->setStatus('PROCESSING');
    $this->em->persist($liquidacion);
    $this->em->flush();

    try {

      //AHORA DEBO ITERAR POR CADA cierre:
      $sql = "SELECT MAX(DATE_FORMAT(STR_TO_DATE(INICIO,'%d-%m-%Y %h:%i'),'%Y-%m-%d')) as MAX_INICIO,MIN(DATE_FORMAT(STR_TO_DATE(INICIO,'%d-%m-%Y %h:%i'),'%Y-%m-%d')) as MIN_INICIO FROM cierre WHERE archivo_de_cierre_id = ".$liquidacion->getArchivoDeCierre()->getId()." AND BAREMOS IS NULL AND PRECIO IS NULL";
      $stmt = $this->em->getConnection()->prepare($sql);
      $stmt->execute();

      foreach ($stmt->fetchAll() as $cierre) {
         //AHORA DEBO BUSCAR SI HAY AL MENOS UN PRECIO EN DICHO RANGO DE FECHAS:
        $sql_aux = "SELECT 1 FROM Precio WHERE '".$cierre['MAX_INICIO']."' BETWEEN fecha_desde AND fecha_hasta;";
        $stmt_aux = $this->em->getConnection()->prepare($sql_aux);
        $stmt_aux->execute();
        if ($stmt_aux->rowCount() == 0) throw new \Doctrine\DBAL\DBALException("NO EXISTE PRECIO PARA FECHA ".$cierre['MAX_INICIO']);

        $sql_aux = "SELECT 1 FROM Precio WHERE '".$cierre['MIN_INICIO']."' BETWEEN fecha_desde AND fecha_hasta;";
        $stmt_aux = $this->em->getConnection()->prepare($sql_aux);
        $stmt_aux->execute();
        if ($stmt_aux->rowCount() == 0) throw new \Doctrine\DBAL\DBALException("NO EXISTE PRECIO PARA FECHA ".$cierre['MIN_INICIO']);

      }

      //AHORA BUSCO UNA ITERACIÓN POR CADA cierre:
      $sql = "SELECT id,DATE_FORMAT(STR_TO_DATE(INICIO,'%d-%m-%Y %h:%i'),'%Y-%m-%d') as INICIO,UCASE(MARCA_RECURSO_INSUFICIENTE) as MARCA_RECURSO_INSUFICIENTE,UCASE(CUADRILLA_DESASIGNADA) as CUADRILLA_DESASIGNADA,COD_MOVIL,TIPO_MOVIL,UCASE(ESTADO) as ESTADO, UCASE(ESTADO_DE_FINALIZACION) as ESTADO_DE_FINALIZACION,CODIGO_AMBITO,CODIGO_ELEMENTO_RESPONSABLE, CODIGO_CONDICION FROM cierre WHERE archivo_de_cierre_id = ".$liquidacion->getArchivoDeCierre()->getId()." AND BAREMOS IS NULL AND PRECIO IS NULL";
      $stmt = $this->em->getConnection()->prepare($sql);
      $stmt->execute();

      $output->writeln("  * BUSCO CIERRES PARA: #".$stmt->rowCount());
      $i = 0;
      $total = $stmt->rowCount();

      foreach ($stmt->fetchAll() as $cierre) {
        //AHORA BUSCO LA COMBINATORIA:
        $baremo = $this->buscarBaremo($cierre);
        //BAREMO PUEDE VENIR VACIO - EN ESE CASO LANZA UN ERROR Y NO CONTINUA PROCESO.
        //$output->writeln("  * ".$i);
        if (count($baremo) == 0) continue;

        $output->writeln("  FOUND * ".$i." > BAREMOS: ".$baremo['BAREMOS']." $: ".$baremo['PRECIO']);
        //ACTUALIZO EL CIERRE EN CUESTION:
        $sql = "UPDATE cierre SET BAREMOS = '".$baremo['BAREMOS']."', PRECIO = '".$baremo['PRECIO']."' WHERE id = ".$cierre['id'];
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $i++;
        
      }

      if ($i == $total) {
        $liquidacion->setStatus('DONE');
        $this->em->persist($liquidacion);
        $this->em->flush();
      } else {
        $liquidacion->setStatus('HAS_NO_CONFORMITIES');
        $this->em->persist($liquidacion);
        $this->em->flush();
      }

    } catch (\Doctrine\DBAL\DBALException $e) {
         $liquidacion->setStatus("ERROR");
         $msg = " ERROR: ".$e->getMessage();
         $output->writeln("  * ".$msg);
         //DESCOMENTAR EN CASO DE ERROR:
         $liquidacion->setDescription($liquidacion->getDescription().chr(10).$msg);
         $this->em->persist($liquidacion);
         $this->em->flush();
    }

    //AHORA CREO EL ARCHIVO DE CIERRE, PERO DE CSV PARA SER DESCARGADO:
    $file = $this->getContainer()->get('kernel')->getRootDir().'/Resources/data/cierres/liquidacion-'.$liquidacion->getId().'-red-data.csv';
    $this->crearArchivosDeLiquidaciones($file,'TIPO_MOVIL = "SERVICIO DE EMERGENCIA RED" AND (CODIGO_AMBITO NOT IN (6,7,8,9) AND CODIGO_CONDICION NOT IN (86,88,94,95,93))',$liquidacion);

    $file = $this->getContainer()->get('kernel')->getRootDir().'/Resources/data/cierres/liquidacion-'.$liquidacion->getId().'-domicilio-data.csv';
    $this->crearArchivosDeLiquidaciones($file,'TIPO_MOVIL = "DOMICILIARIA EMERGENCIA" AND (CODIGO_AMBITO NOT IN (6,7,8,9) AND CODIGO_CONDICION NOT IN (86,88,94,95,93))',$liquidacion);

    $file = $this->getContainer()->get('kernel')->getRootDir().'/Resources/data/cierres/liquidacion-'.$liquidacion->getId().'-moto-data.csv';
    $this->crearArchivosDeLiquidaciones($file,'TIPO_MOVIL IN ("MOTO ELECTRICISTA","MOTO PRIORIDAD 1") AND (CODIGO_AMBITO NOT IN (6,7,8,9) AND CODIGO_CONDICION NOT IN (86,88,94,95,93))',$liquidacion);

    $file = $this->getContainer()->get('kernel')->getRootDir().'/Resources/data/cierres/liquidacion-'.$liquidacion->getId().'-corte_y_reposicion-data.csv';
    $this->crearArchivosDeLiquidaciones($file,' (CODIGO_AMBITO IN (6,7,8,9) AND CODIGO_CONDICION IN (86,88,94,95,93))',$liquidacion);

    //AHORA CREO EL ARCHIVO DE PRECIOS FALTANTES
    $file = $this->getContainer()->get('kernel')->getRootDir().'/Resources/data/cierres/liquidacion-'.$liquidacion->getId().'-precios-faltantes-data.csv';
    
    if (file_exists($file)) unlink($file);
    file_put_contents($file, "");            
    if(!is_writable($file)) throw new \Exception("FILE ".$file." NOT WRITEABLE.");
  
    //Escribo la cabecera:
    $fp = fopen($file, 'w');
    $cabecera = array(
      "BAREMO",
      "PRECIO",
      "COD ELEMENTO AVERIADO",
      "DESCRIPCION ELEMENTO AVERIADO",
      "CODIGO AMBITO",
      "DESCRIPCION AMBITO",
      "COD CONDICION",
      "DESCRIPCION CONDICION",
      "ESTADO_DE_FINALIZACION",
      "CODIGOS",
      "ESTADO",
      "RECURSO INSUF",
      "CUADRILLA DESASIGNADA",
      "COD MOVIL",
      "TIPO MOVIL"
      );
    fputcsv($fp, $cabecera,';','"');

    $sql = "SELECT 
    BAREMOS, 
    PRECIO,
    CODIGO_ELEMENTO_RESPONSABLE,
    '-DESCRIPCION ELEMENTO RESPONSABLE-',
    CODIGO_AMBITO,
    '-DESCRIPCION AMBITO-',
    CODIGO_CONDICION, 
    '-DESCRIPCION CONDICION-',
    UCASE(ESTADO_DE_FINALIZACION) as ESTADO_DE_FINALIZACION, 
    CONCAT(CODIGO_AMBITO,CODIGO_ELEMENTO_RESPONSABLE,CODIGO_CONDICION),
    UCASE(ESTADO) as ESTADO,
    UCASE(MARCA_RECURSO_INSUFICIENTE) as MARCA_RECURSO_INSUFICIENTE,
    UCASE(CUADRILLA_DESASIGNADA) as CUADRILLA_DESASIGNADA, 
    COD_MOVIL,
    UCASE(TIPO_MOVIL) as TIPO_MOVIL
    FROM cierre WHERE archivo_de_cierre_id = ".$liquidacion->getArchivoDeCierre()->getId()." AND PRECIO IS NULL AND BAREMOS IS NULL";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    foreach ($stmt->fetchAll() as $info) {
      fputcsv($fp, $info,';','"');
    }
    fclose($fp);

    //FINALMENTE ESCRIBO EL INDICE:
    $indice = array();
    $sql =  "SELECT COUNT(*) as total FROM cierre WHERE archivo_de_cierre_id = ".$liquidacion->getArchivoDeCierre()->getId()." AND BAREMOS IS NOT NULL AND PRECIO IS NOT NULL";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    foreach ($stmt->fetchAll() as $info) {
      $indice['OK'] = $info['total'];
    }

    $sql =  "SELECT COUNT(*) as total FROM cierre WHERE archivo_de_cierre_id = ".$liquidacion->getArchivoDeCierre()->getId()."";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    foreach ($stmt->fetchAll() as $info) {
      $indice['TOTAL'] = $info['total'];
    }

    $liquidacion->setIndice($indice['OK'].' eventos con precios de un total de '.$indice['TOTAL']);
    $this->em->persist($liquidacion);
    $this->em->flush();
    
  }  

  private function crearArchivosDeLiquidaciones($file,$where = " 1", $liquidacion) {

    if (file_exists($file)) unlink($file);
    file_put_contents($file, "");            
    if(!is_writable($file)) throw new \Exception("FILE ".$file." NOT WRITEABLE.");
  
    //Escribo la cabecera:
    $fp = fopen($file, 'w');
    $cabecera = array("UID-ID","EVENTO","MARCA RECURSO INSUFICIENTE","CUADRILLA DESASIGNADA","COD MOVIL","TIPO MOVIL","EMPRESA","SUPERVISOR","FECHA DESPACHO","FECHA ACEPTACION","FECHA RUTA","FECHA LIBERACION","ESTADO","TIPO EVENTO","COMUNA","INICIO","ARRIBADO","FIN","PDA FECHA CONTACTO ESTIMADO","PDA FECHA TERMINO ESTIMADO","PDA FECHA CONTACTO REAL","PDA FECHA TERMINO REAL","ESTADO DE FINALIZACION","OBSERVACION CUMPLIMENTACION","LLEGADA EVENTO","ASIGNADA","ACEPTADA","EN RUTA","ARRIBO","LIBERADA","CUMPL.PROMESA","CODIGO AMBITO","CODIGO ELEMENTO RESPONSABLE","CODIGO CONDICION","DESCRIPCION AMBITO","DESCRIPCION ELEMENTO RESPONSABLE","DESCRIPCION CONDICION","BAREMOS","PRECIO");
    fputcsv($fp, $cabecera,';','"');

    $sql = "SELECT id,  EVENTO,  MARCA_RECURSO_INSUFICIENTE,  CUADRILLA_DESASIGNADA,  COD_MOVIL,TIPO_MOVIL,EMPRESA,SUPERVISOR,FECHA_DESPACHO,FECHA_ACEPTACION,FECHA_RUTA,FECHA_LIBERACION,                ESTADO , TIPO_EVENTO,COMUNA,INICIO,       ARRIBADO,FIN,PDA_FECHA_CONTACTO_ESTIMADO,        PDA_FECHA_TERMINO_ESTIMADO,PDAFECHA_CONTACTO_REAL,PDA_FECHA_TERMINO_REAL,      ESTADO_DE_FINALIZACION,OBSERVACION_CUMPLIMENTACION,LLEGADA_EVENTO,ASIGNADA,ACEPTADA,EN_RUTA,ARRIBO,LIBERADA,CUMPL_PROMESA,CODIGO_AMBITO,CODIGO_ELEMENTO_RESPONSABLE,CODIGO_CONDICION,DESCRIPCION_AMBITO,DESCRIPCIONELEMENTO_RESPONSABLE,DESCRIPCION_CONDICION,BAREMOS,PRECIO FROM cierre WHERE archivo_de_cierre_id = ".$liquidacion->getArchivoDeCierre()->getId()." AND ".$where;
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    foreach ($stmt->fetchAll() as $info) {
      fputcsv($fp, $info,';','"');
    }
    fclose($fp);

  }

  private function buscarBaremo($cierre) {

    if ($cierre['ESTADO'] <> 'CERRADO') {
      if ($cierre['ESTADO'] <> '*') return array('BAREMOS' => ' -- NO APLICA POR CIERRE --', 'PRECIO' => 0);
    }
    
    //PRIMERO BUSCO SI HAY PRECIOS, MARCA RECURSO INSUFICIENTE, CUADRILLA_DESASIGNADA, ESTADO_DE_FINALIZACION EN ESTE RANGO DEVOLVIENDO EL SET DE ID:
    $sql = "SELECT id FROM precio_item WHERE ESTADO_DE_FINALIZACION IN ('*','".$cierre['ESTADO_DE_FINALIZACION']."') AND MARCA_RECURSO_INSUFICIENTE = '".$cierre['MARCA_RECURSO_INSUFICIENTE']."' AND CUADRILLA_DESASIGNADA = '".$cierre['CUADRILLA_DESASIGNADA']."' AND '".$cierre['INICIO']."' BETWEEN fecha_desde AND fecha_hasta";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() == 0) return array();

    //AHORA VEO SI HAY UN VALOR PARA TODO; SI NO LO HAY DEBO BUSCAR POR LOS COMODIN *:
    $sql = "SELECT id,BAREMOS,PRECIO FROM precio_item WHERE COD_MOVIL IN ('".$cierre['COD_MOVIL']."','*') AND MARCA_RECURSO_INSUFICIENTE = '".$cierre['MARCA_RECURSO_INSUFICIENTE']."' AND CUADRILLA_DESASIGNADA = '".$cierre['CUADRILLA_DESASIGNADA']."' AND '".$cierre['INICIO']."' BETWEEN fecha_desde AND fecha_hasta AND TIPO_MOVIL = '".$cierre['TIPO_MOVIL']."' AND ESTADO IN ('".$cierre['ESTADO']."','*') AND ESTADO_DE_FINALIZACION  IN ('".$cierre['ESTADO_DE_FINALIZACION']."','*') AND CODIGO_AMBITO  = '".$cierre['CODIGO_AMBITO']."' AND CODIGO_ELEMENTO_RESPONSABLE = '".$cierre['CODIGO_ELEMENTO_RESPONSABLE']."' AND CODIGO_CONDICION  = '".$cierre['CODIGO_CONDICION']."'";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
      foreach ($stmt->fetchAll() as $fetch) {
        return array('BAREMOS' => $fetch['BAREMOS'], 'PRECIO' =>  $fetch['PRECIO']);
      }
    }

    //REDUZCO AHORA AL ESTADO EN CONCRETO
    $sql = "SELECT id,BAREMOS,PRECIO FROM precio_item WHERE COD_MOVIL IN ('".$cierre['COD_MOVIL']."','*') AND MARCA_RECURSO_INSUFICIENTE = '".$cierre['MARCA_RECURSO_INSUFICIENTE']."' AND CUADRILLA_DESASIGNADA = '".$cierre['CUADRILLA_DESASIGNADA']."' AND '".$cierre['INICIO']."' BETWEEN fecha_desde AND fecha_hasta AND TIPO_MOVIL = '".$cierre['TIPO_MOVIL']."' AND ESTADO IN ('".$cierre['ESTADO']."') AND ESTADO_DE_FINALIZACION  IN ('".$cierre['ESTADO_DE_FINALIZACION']."','*') AND CODIGO_AMBITO  = '".$cierre['CODIGO_AMBITO']."' AND CODIGO_ELEMENTO_RESPONSABLE = '".$cierre['CODIGO_ELEMENTO_RESPONSABLE']."' AND CODIGO_CONDICION  = '".$cierre['CODIGO_CONDICION']."'";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
      foreach ($stmt->fetchAll() as $fetch) {
        return array('BAREMOS' => $fetch['BAREMOS'], 'PRECIO' =>  $fetch['PRECIO']);
      }
    }

    //REDUZCO AHORA AL ESTADO DE FINALIZACION EN CONCRETO
    $sql = "SELECT id,BAREMOS,PRECIO FROM precio_item WHERE COD_MOVIL IN ('".$cierre['COD_MOVIL']."','*') AND MARCA_RECURSO_INSUFICIENTE = '".$cierre['MARCA_RECURSO_INSUFICIENTE']."' AND CUADRILLA_DESASIGNADA = '".$cierre['CUADRILLA_DESASIGNADA']."' AND '".$cierre['INICIO']."' BETWEEN fecha_desde AND fecha_hasta AND TIPO_MOVIL = '".$cierre['TIPO_MOVIL']."' AND ESTADO IN ('".$cierre['ESTADO']."') AND ESTADO_DE_FINALIZACION  IN ('".$cierre['ESTADO_DE_FINALIZACION']."') AND CODIGO_AMBITO  = '".$cierre['CODIGO_AMBITO']."' AND CODIGO_ELEMENTO_RESPONSABLE = '".$cierre['CODIGO_ELEMENTO_RESPONSABLE']."' AND CODIGO_CONDICION  = '".$cierre['CODIGO_CONDICION']."'";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
      foreach ($stmt->fetchAll() as $fetch) {
        return array('BAREMOS' => $fetch['BAREMOS'], 'PRECIO' =>  $fetch['PRECIO']);
      }
    }

    if ($stmt->rowCount() > 1) return array(); //HAY MAS DE 1 PRECIO POSIBLE.

    //PRUEBO CON UN COMODIN
    $sql = "SELECT id, TIPO_MOVIL,UCASE(ESTADO) as ESTADO, UCASE(ESTADO_DE_FINALIZACION) as ESTADO_DE_FINALIZACION,CODIGO_AMBITO,CODIGO_ELEMENTO_RESPONSABLE, CODIGO_CONDICION, BAREMOS,PRECIO FROM precio_item WHERE COD_MOVIL = '".$cierre['COD_MOVIL']."' AND MARCA_RECURSO_INSUFICIENTE = '".$cierre['MARCA_RECURSO_INSUFICIENTE']."' AND CUADRILLA_DESASIGNADA = '".$cierre['CUADRILLA_DESASIGNADA']."' AND '".$cierre['INICIO']."' BETWEEN fecha_desde AND fecha_hasta AND TIPO_MOVIL IN ('".$cierre['TIPO_MOVIL']."','*') AND ESTADO IN ('".$cierre['ESTADO']."','*') AND ESTADO_DE_FINALIZACION  IN ('".$cierre['ESTADO_DE_FINALIZACION']."','*') AND CODIGO_AMBITO  IN ('".$cierre['CODIGO_AMBITO']."','*') AND CODIGO_ELEMENTO_RESPONSABLE IN ('".$cierre['CODIGO_ELEMENTO_RESPONSABLE']."','*') AND CODIGO_CONDICION  IN ('".$cierre['CODIGO_CONDICION']."','*')";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
      foreach ($stmt->fetchAll() as $fetch) {
        return array('BAREMOS' => $fetch['BAREMOS'], 'PRECIO' =>  $fetch['PRECIO']);
      }
    }

    if ($stmt->rowCount() == 0) return array();

    $output->writeln("  * FILTRO DE CANTIDAD.");
    //HAY MAS DE DOS OPCIONES: ELIMINO LA QUE TENGA MAS *, PUES DEBO QUEDARME SIEMPRE CON LA DE MENOS ESPECTRO:
    $flags = array();
    foreach ($stmt->fetchAll() as $fetch) {
      $flags[$fetch['id']] = count(array_keys($fetch,'*',true));
    }

    return array();
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