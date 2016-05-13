<?php

namespace BcTic\CamSotBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use BcTic\CamSotBundle\Form\Type\ColumnaCsvType;

class PrecioType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('descripcion','textarea', array('label' => 'Descripción'))
            ->add('moviles','textarea', array('label' => 'Código móviles separados por coma, un asterisco si es cualquier.'))
            ->add('tipo_moviles','textarea', array('label' => 'Tipo móviles separados por coma, un asterisco si es cualquier.'))
            ->add('status','hidden')
            ->add('fecha_desde','date', array(
                'widget' => 'choice',
                'input' => 'datetime',
                'format' => 'ddMMyyyy',
                'label' => 'Desde'
                ))
            ->add('fecha_hasta','date', array(
                'widget' => 'choice',
                'input' => 'datetime',
                'format' => 'ddMMyyyy',
                'label' => 'Hasta'
                ))
            ->add('columnas', new ColumnaCsvType())
            ->add('file','file', array('label' => 'Archivo de precios a importar (* Máx 50Mb)'))
            ->add('columns_separator','choice',array('label' => 'Separador de columnas CSV', 'choices' => array(';' => ';','|' => '|') ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BcTic\CamSotBundle\Entity\Precio'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bctic_camsotbundle_precio';
    }
}
