<?php 

namespace BcTic\CamSotBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class ColumnaType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'columna';
    }
}