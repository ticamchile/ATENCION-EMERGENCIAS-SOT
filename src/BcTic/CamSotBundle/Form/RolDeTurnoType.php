<?php

namespace BcTic\CamSotBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RolDeTurnoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('turno')
            ->add('fecha','date', array(
              'input'  => 'datetime',
              'widget' => 'choice',
              'format' => 'ddMMyyyy',
             ))
            ->add('rolesEnTurno', 'collection', array('type' => new RolEnTurnoType()))
            ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BcTic\CamSotBundle\Entity\RolDeTurno'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bctic_camsotbundle_roldeturno';
    }
}
