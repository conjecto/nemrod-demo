<?php

namespace Conjecto\Bundle\DemoBundle\Form;

use Conjecto\Nemrod\Form\Extension\Core\Type\ResourceFormType;
use Conjecto\Nemrod\ResourceManager\Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class AwardType extends ResourceFormType
{

    protected $rm;

    public function __construct($rm){
        $this->rm = $rm;
        return parent::__construct($rm);
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('terms:year', 'integer', array('label' => 'year'));
        $builder->add('terms:motivation', 'text', array('label' => 'motivation'));
        $builder->add('terms:field', 'text', array('label' => 'nom'));
        $builder->add('terms:category', 'text', array('label' => 'category'));
        $builder->add('terms:laureate', new LaureateType($this->rm), array(
            'data_class' => 'Conjecto\Bundle\DemoBundle\RdfResource\Laureate',
            'empty_data' => function () use ($options){
            return $this->rm->getRepository('terms:Laureate')->create();
        }));

    }

    public function getParent()
    {
        return 'resource_form';
    }

    public function getName()
    {
        return 'award_type';
    }
} 