<?php

namespace Conjecto\Bundle\DemoBundle\Form;

use Conjecto\Nemrod\Form\Extension\Core\Type\ResourceFormType;
use Conjecto\Nemrod\ResourceManager\Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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

        $builder->add('terms:year', 'integer', array('label' => 'Year'));
        $builder->add('terms:category', 'resource', [
            'label' => 'Category',
            'expanded' => false,
            'multiple' => false,
            'class' => 'terms:Category',
            'property' => 'rdfs:label',
            'query_builder' => function (Repository $repo) {
                $qb = $repo->getQueryBuilder();
                $qb->reset();
                $qb->construct();
                $qb->where('?s a terms:Category; rdfs:label ?label');
                return $qb;
            }]);
        $builder->add('terms:field', 'text', array('label' => 'Field'));
        $builder->add('terms:motivation', 'textarea', array('label' => 'Motivation'));



        $builder->add('terms:laureate', new LaureateType($this->rm), array(
            'label' => 'Laureate',
            'data_class' => 'Conjecto\Bundle\DemoBundle\RdfResource\Laureate',
            'empty_data' => function () use ($options){
            return $this->rm->getRepository('terms:Laureate')->create();
        }));

        //listener for setting rdfs:label fields
        $builder->get('terms:laureate')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) {
                $laureate = $event->getForm()->getData();
                $label = $laureate->get('foaf:familyName') . " " . $laureate->get('foaf:givenName');

                $laureate->set('rdfs:label', $label);
            }
        );

        //listener for setting rdfs:label fields
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function(FormEvent $event) {
                $laureateaward = $event->getForm()->getData();
                $laureate = $event->getForm()->get("terms:laureate")->getData();
                $label = $laureateaward->get('terms:category/rdfs:label') . ' ' . $laureateaward->get('terms:year') . ', ' . $laureate->get('foaf:familyName') . " " . $laureate->get('foaf:givenName');

                $laureateaward->set('rdfs:label', $label);
            }
        );

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