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
            ->add('nom','text')
            ->add('description','text')
            ->add('categorie','text')
            ->add('tags','text')
            ->add('date_debut','datetime')
            ->add('date_fin','datetime')
            ->add('lieu','text')
            ->add('emplacement','text')
            ->add('create','submit');
    }

    public function getName()
    {
        return 'app_event';
    }
}