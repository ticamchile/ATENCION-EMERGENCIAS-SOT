<?php

namespace BcTic\CamSotBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CierreFilterType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('archivoDeCierre','entity', array(
                  'class' => 'BcTic\CamSotBundle\Entity\ArchivoDeCierre'
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BcTic\CamSotBundle\Entity\Cierre'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bctic_camsotbundle_cierre';
    }
}
