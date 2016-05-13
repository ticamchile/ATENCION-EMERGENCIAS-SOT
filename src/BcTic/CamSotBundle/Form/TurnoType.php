<?php

namespace BcTic\CamSotBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TurnoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dia','choice', array(
                'label' => 'Tipo',
                'choices' => array(
                    'DOMICILIO' => 'DOMICILIO',  
                    'LIBRE_DOMICILIO' => 'DIA LIBRE DOMICILIO',
                    'LICENCIA_MEDICA_DOMICILIO' => 'LICENCIA MÉDICA DOMICILIO',
                    'RED' => 'RED',
                    'LIBRE_RED' => 'DIA LIBRE RED',
                    'LICENCIA_MEDICA_RED' => 'LICENCIA MÉDICA RED',
                    )
                ))
            ->add('periodo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BcTic\CamSotBundle\Entity\Turno'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bctic_camsotbundle_turno';
    }
}
