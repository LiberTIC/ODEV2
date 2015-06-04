<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom','text', array(
                'label' => "Titre (*)",
                'attr' => array(
                    'placeholder' => "Titre",
                )
            ))
            ->add('description','textarea',array(
                'label' => "Description (*)",
                )
            )
            ->add('categorie','text')
            ->add('tags','text')
            ->add('date_debut', 'datetime',array(
                'widget' => 'single_text',
                'horizontal_input_wrapper_class' => 'col-lg-5',
                'datetimepicker' => true,
                'attr' => array('placeholder' => '4 Juin 2015 - 20:30')
            ))
            ->add('date_fin', 'datetime',array(
                'widget' => 'single_text',
                'horizontal_input_wrapper_class' => 'col-lg-5',
                'datetimepicker' => true,
                'attr' => array('placeholder' => '4 Juin 2015 - 23:30')
            ))
            ->add('lieu','text', array(
                'label' => "Lieu",
                'attr' => array(
                    'placeholder' => "Stéréolux, Nantes, France"
                    )
                )
            )
            ->add('emplacement','text', array(
                'label' => "Emplacement",
                'attr' => array(
                    'placeholder' => "Salle de concert n°3"
                    )
                )
            );
    }

    public function getName()
    {
        return 'app_event';
    }
}