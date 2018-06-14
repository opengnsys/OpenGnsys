<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */

namespace Opengnsys\ServerBundle\Form\Type\Api;

use Opengnsys\ServerBundle\Entity\Command;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('script')
            ->add('parameters')
            ->add('type')
            //->add('clients', )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Command::class,
        	'cascade_validation' => true,
        	'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'opengnsys_server__api_form_type_command';
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return $this->getName();
    }
}
