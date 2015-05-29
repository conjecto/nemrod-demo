<?php

namespace Conjecto\Bundle\DemoBundle\Form;

use Conjecto\Nemrod\Form\Extension\Core\Type\ResourceFormType;
use Conjecto\Nemrod\ResourceManager\Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class LaureateType extends ResourceFormType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('foaf:givenName', 'text', array('label' => 'Given name'));
        $builder->add('foaf:gender', 'choice', array(
            'label' => 'Gender',
            'choices'   => array('male' => 'Male', 'female' => 'Female'),
            'required'  => false,
        ));

        $builder->add('foaf:familyName', 'text', array('label' => 'Family name'));
        $builder->add('foaf:birthday', 'date', array(
            'years'         => range(1800 , date('Y')),
            'label' => 'Date of birth')
        );
        $builder->add('dbpprop:dateOfDeath', 'date', array(
            'label' => 'Date of death',
            'empty_value' => '',
            'empty_data' => null,
            'required' => false,
            'years'         => range(1900 , date('Y')),
            )
        );
    }

    public function getName()
    {
        return 'resource_laureate';
    }

    public function getParent()
    {
        return 'resource_form';
    }
} 