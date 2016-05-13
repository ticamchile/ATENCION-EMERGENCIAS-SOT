<?php 

namespace BcTic\CamSotBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use BcTic\CamSotBundle\Form\Type\ColumnaType;

class ColumnaCsvType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
           'type' => new ColumnaType(),
           'options'  => array(
                    'choices' => array(
                        'BAREMOS' => 'BAREMOS',
                        'PRECIO' => 'PRECIO',
                        'CODIGO_ELEMENTO_RESPONSABLE' => 'COD ELEMENTO AVERIADO',
                        'IGNORAR_0' => 'IGNORAR',
                        'CODIGO_AMBITO' => 'CODIGO AMBITO',
                        'IGNORAR_1' => 'IGNORAR',
                        'CODIGO_CONDICION' => 'CÓDIGO CONDICIÓN',
                        'IGNORAR_2' => 'IGNORAR',
                        'ESTADO_DE_FINALIZACION' => 'ESTADO DE FINALIZACION',
                        'IGNORAR_3' => 'IGNORAR',
                        'ESTADO' => 'ESTADO (CERRADO)',
                        'MARCA_RECURSO_INSUFICIENTE' => 'MARCA RECURSO INSUFICIENTE (NO)',
                        'CUADRILLA_DESASIGNADA' => 'CUADRILLA DESASIGNADA  (NO)',
                        'COD_MOVIL' => 'CÓDIGO MOVIL (SOBRE-ESCRIBE VALOR SI EXISTE)',
                        'TIPO_MOVIL' => 'TIPO MOVIL (SOBRE-ESCRIBE VALOR SI EXISTE)',
                    )),
                ));
    }

    public function getParent()
    {
        return 'collection';
    }

    public function getName()
    {
        return 'columna_csv';
    }
}