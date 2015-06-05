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
                'attr' => array('placeholder' => "Concert de Shakaponk"),
                )
            )
            ->add('description','textarea',array(
                'label' => "Description (*)",
                'attr' => array('placeholder' => "Tournée pour la sortie de leur nouvel album [...]"),
                )
            )
            ->add('categorie','text',array(
                'label' => "Catégorie (*)",
                'attr' => array('placeholder' => "Musique")
                )
            )
            ->add('tags','text', array(
                'label' => "Tags",
                'required' => false,
                'attr' => array('placeholder' => "Concert; Musique; Shakaponk; Rock; [...]")
                )
            )
            ->add('langue','text', array(
                'label' => "Langue de l'événement",
                'required' => false,
                'attr' => array('placeholder' => "fr"),
                'horizontal_input_wrapper_class' => 'col-lg-2',
                'help_block' => 'fr, en, es, ...'
                )
            )
            /* So, there is this little bug where you can't change the icon of a datetime without breaking things.
             * Which mean that if you want to change the icon, you have to tweak the code in Mopa\Bundle\BootstrapBundle\Resources\views\Form\fields.html.twig
             * There is 2 ways:
             *      1- Line 355, replace 'th' with your icon (ex: calendar)
             *      2- Comment line 266 and use 'widget_addon_append' here
             */
            ->add('date_debut', 'datetime',array(
                'widget' => 'single_text',
                'horizontal_input_wrapper_class' => 'col-lg-5',
                'datetimepicker' => true,
                'label' => 'Date de début (*)',
                'attr' => array('placeholder' => '4 Juin 2015 - 20:30')
                )
            )
            ->add('date_fin', 'datetime',array(
                'widget' => 'single_text',
                'horizontal_input_wrapper_class' => 'col-lg-5',
                'datetimepicker' => true,
                'label' => 'Date de fin (*)',
                'attr' => array('placeholder' => '4 Juin 2015 - 23:30')
                )
            )
            ->add('lieu','text', array(
                'label' => "Lieu",
                'required' => false,
                'attr' => array('placeholder' => "Stéréolux, Nantes, France")
                )
            )
            ->add('emplacement','text', array(
                'label' => "Emplacement",
                'required' => false,
                'attr' => array('placeholder' => "Salle de concert n°3")
                )
            )
            ->add('capacite_lieu','number', array(
                'label' => 'Capacité du lieu',
                'required' => false,
                'attr' => array('placeholder' => "350"),
                'help_block' => 'En nombre de personnes'
                )
            )
            ->add('participants','text', array(
                'label' => 'Participants',
                'required' => false,
                'attr' => array('placeholder' => 'Shaka Ponk; Lisa Leblanc; [...]')
                )
            )
            ->add('duree','text', array(
                'label' => 'Durée',
                'required' => false,
                'attr' => array('placeholder' => '2h30 à 3h00'),
                //'help_block' => 'Seulement si la durée est inférieur à la différence entre la date de début et de fin',
                'widget_addon_append' => array('icon' => 'time'),
                'horizontal_input_wrapper_class' => 'col-lg-4'
                )
            )
            ->add('status','choice', array(
                'label' => 'Status',
                'choices' => array('1' => 'Confirmé', '2' => 'Annulé', '3' => 'Reporté'),
                'horizontal_input_wrapper_class' => 'col-lg-4'
                )
            )
            ->add('organisateur','text', array(
                'label' => 'Organisateur',
                'required' => false,
                'attr' => array('placeholder' => 'Association Stéréolux'),
                )
            )
            ->add('url_orga','text', array(
                'label' => 'Url Organisateur',
                'required' => false,
                'attr' => array('placeholder' => 'http://www.stereolux.org'),
                'widget_addon_append' => array('icon' => 'globe'),
                )
            )
            ->add('urls_medias','collection', array(
                'type' => 'text',
                'allow_add' => true,
                'required' => false,
                'allow_delete' => true,
                'prototype' => true,
                'widget_add_btn' => array('label' => "Ajouter url"),
                'show_legend' => false,
                'horizontal_wrap_children' => true,
                'help_block' => 'Les urls doivent être compatible <b><a href="#oembed" onclick="$(\'#oembed\').effect(\'highlight\', {}, 3000);">oEmbed</a></b>',
                'options' => array(
                    'label_render' => false,
                    'attr' => array('placeholder' => 'https://youtu.be/aRljVackZ08'),
                    'widget_addon_prepend' => array(
                        'icon' => 'globe',
                        ),
                    'widget_remove_btn' => array(
                        'label' => "Supprimer",
                        'horizontal_wrapper_div' => array(
                            'class' => "col-lg-4"
                            ),
                        'wrapper_div' => false,
                        ),
                    'horizontal' => true,
                    'horizontal_label_offset_class' => "",
                    'horizontal_input_wrapper_class' => "col-lg-8",
                    )
                )
            )
            ->add('contact_nom','text', array(
                'label' => 'Nom du contact',
                'required' => false,
                'attr' => array('placeholder' => 'John Smith')
                )
            )
            ->add('contact_email','email', array(
                'label' => 'Email du contact',
                'required' => false,
                'attr' => array('placeholder' => 'john.smith@stereolux.org')
                )
            )
            ->add('prix_standard','money', array(
                'label' => 'Prix standard',
                'required' => false,
                'attr' => array('placeholder' => '25'),
                'horizontal_input_wrapper_class' => 'col-lg-3',
                )
            )
            ->add('prix_reduit','money', array(
                'label' => 'Prix réduit',
                'required' => false,
                'attr' => array('placeholder' => '25'),
                'horizontal_input_wrapper_class' => 'col-lg-3',
                )
            )
            ->add('prix_enfant','money', array(
                'label' => 'Prix enfant',
                'required' => false,
                'attr' => array('placeholder' => '25'),
                'horizontal_input_wrapper_class' => 'col-lg-3',
                )
            )
            ;
    }

    public function getName()
    {
        return 'app_event';
    }
}