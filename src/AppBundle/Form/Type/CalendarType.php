<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayName','text',array(
                'label' => 'Nom',
                'attr' => array('placeholder' => 'Concert saison 2015-2016')
                )
            );
    }

    public function getName()
    {
        return 'app_calendar';
    }
}