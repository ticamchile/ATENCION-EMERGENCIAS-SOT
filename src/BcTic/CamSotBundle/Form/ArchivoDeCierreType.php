<?php

namespace BcTic\CamSotBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ArchivoDeCierreType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fechaDeInicio','date', array(
                'widget' => 'choice',
                'input' => 'datetime',
                'format' => 'ddMMyyyy',
                'label' => 'Eventos desde'
                ))
            ->add('fechaDeTermino','date', array(
                'widget' => 'choice',
                'input' => 'datetime',
                'format' => 'ddMMyyyy',
                'label' => 'Eventos hasta'
                ))
            ->add('descripcion','textarea', array('label' => 'Descripción'))
            ->add('createdAt','hidden')
            ->add('status','hidden')
            ->add('path','hidden')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BcTic\CamSotBundle\Entity\ArchivoDeCierre'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bctic_camsotbundle_archivodecierre';
    }
}
