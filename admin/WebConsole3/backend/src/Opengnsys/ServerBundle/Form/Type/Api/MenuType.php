<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */

namespace Opengnsys\ServerBundle\Form\Type\Api;

use Opengnsys\ServerBundle\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description')
            ->add('idurlimg')
            ->add('title')
            ->add('publicxcoordinate')
            ->add('publicycoordinate')
            ->add('publicmode')
            ->add('privatexcoordinate')
            ->add('privateycoordinate')
            ->add('privatemode')
            ->add('comments')
            ->add('publicmenuhtml')
            ->add('privatemenuhtml')
            ->add('resolution')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Menu::class,
        	'cascade_validation' => true,
        	'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'opengnsys_server__api_form_type_menu';
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return $this->getName();
    }
}
