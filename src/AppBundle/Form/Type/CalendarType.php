<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CalendarType
 *
 * @package AppBundle\Form\Type
 */
class CalendarType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayname', 'text', array(
                'label' => 'Nom',
                'attr' => array('placeholder' => 'Concert saison 2015-2016'),
                )
            )
            ->add('description', 'text', array(
                'label' => 'Description',
                'attr' => array('placeholder' => 'Calendrier des concert de la saison 2015-2016'),
                )
            );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_calendar';
    }
}
