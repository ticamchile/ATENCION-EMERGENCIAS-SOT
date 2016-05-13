<?php

namespace BcTic\CamSotBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RolEnTurnoType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rol')
            ->add('registro','entity', array(
                    'label' => 'Persona',
                    'class' => 'BcTicCamSotBundle:Registro',
                     'required'    => false,
                     'empty_value' => ' -- SIN ASIGNAR --',
                     'empty_data'  => null,
                     'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                          ->orderBy('r.nombre', 'ASC');
                     }
                  ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BcTic\CamSotBundle\Entity\RolEnTurno'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bctic_camsotbundle_rolenturno';
    }
}
