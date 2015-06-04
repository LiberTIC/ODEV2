<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom','text', array(
                'label' => "Label name",
                'help_block' => 'Associated help text',
                'attr' => array(
                    'placeholder' => "Some text",
                )
            ))
            ->add('description','textarea')
            ->add('categorie','text')
            ->add('tags','text')
            ->add('date_debut','genemu_jquerydate', array('widget' => 'single_text'))
            ->add('date_fin','genemu_jquerydate', array('widget' => 'single_text'))
            ->add('lieu','text')
            ->add('emplacement','text');
    }

    public function getName()
    {
        return 'app_event';
    }
}