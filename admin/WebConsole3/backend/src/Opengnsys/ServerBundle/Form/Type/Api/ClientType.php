<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */

namespace Opengnsys\ServerBundle\Form\Type\Api;

use Opengnsys\ServerBundle\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('required'=>true))
            ->add('serialno', null, array('required'=>false))
            ->add('netiface', null, array('required'=>true))
            ->add('netdriver', null, array('required'=>true))
            ->add('mac', null, array('required'=>true))
            ->add('ip', null, array('required'=>true))
            ->add('cache', null, array('required'=>false))
            ->add('idproautoexec', null, array('required'=>false))
            ->add('organizationalUnit', null, array('required'=>true))
            ->add('repository', null, array('required'=>true))
            ->add('hardwareProfile', null, array('required'=>false))
            ->add('oglive', null, array('required'=>false))
            ->add('netboot', null, array('required'=>false))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Client::class,
        	'cascade_validation' => true,
        	'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'opengnsys_server__api_form_type_client';
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return $this->getName();
    }
}
