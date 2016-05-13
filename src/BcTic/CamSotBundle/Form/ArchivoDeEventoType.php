<?php

namespace BcTic\CamSotBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ArchivoDeEventoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('createdAt','hidden')
            ->add('status','hidden')
            ->add('tipo','choice', array('choices' => array(
                 'EVENTOS' => '1 ARCHIVO DE EVENTOS',
                 'RESPONSABLES' => '2 ARCHIVO DE CAUSAS/RESPONSABLES',
                 'CUADRILLAS' => '3 ARCHIVO DE CUADRILLAS/MOVIL',
                 'PROMESAS' => '4 ARCHIVO DE PROMESAS',
                 'PERSONAL' => 'ARCHIVO DE PERSONAL')
                 ))
            ->add('file','file', array('label' => 'Archivo a importar (* Máx 50Mb)'))
            ->add('columns_separator','choice',array('label' => 'Separador de columnas CSV', 'choices' => array(';' => ';',',' => ',','|' => '|') ))
            ->add('notes','textarea',array('label' => 'Notas'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BcTic\CamSotBundle\Entity\ArchivoDeEvento'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bctic_camsotbundle_archivodeevento';
    }
}
