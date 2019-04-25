<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega Alcantara on 15/03/19. <miguelangel.devega@sic.uhu.es>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
namespace Opengnsys\CoreBundle\Domain;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Opengnsys\CoreBundle\Exception\InvalidFormException;

class ModelExtendedManager extends ModelManager
{
    protected $formFactory;
    protected $formTypeClass;
    protected $form;

    public function __construct(EntityManager $entityManager, FormFactoryInterface $formFactory = null, $formTypeClass = null, $objectClass = null)
    {
        parent::__construct($entityManager, $objectClass);

        $this->formFactory = $formFactory;
        $this->formTypeClass = $formTypeClass;
        $this->form = null;
    }

    /**
     * Create a new object.
     *
     * @param array $parameters
     *
     * @return object
     */
    public function post(array $parameters, $flush = true, $validationGroup = null)
    {
        $object = $this->create();

        return $this->persistForm($object, $parameters, 'POST', $flush, $validationGroup);
    }

    /**
     * Create a new object.
     *
     * @param array $parameters
     *
     * @return object
     */
    public function cpost(array $parameters)
    {
        foreach($parameters as $params) {
            $object = $this->create();
            $this->persistForm($object, $params, 'POST', false);
        }
        $this->entityManager->flush();
        return null;
    }

    /**
     * Edit a object.
     *
     * @param $object
     * @param array         $parameters
     *
     * @return object
     */
    public function put($object, array $parameters, $flush = true, $validationGroup = null)
    {
        return $this->persistForm($object, $parameters, 'PUT', $flush, $validationGroup);
    }

    /**
     * Partially update a object.
     *
     * @param $object
     * @param array         $parameters
     *
     * @return object
     */
    public function patch($object, array $parameters, $flush = true, $validationGroup = null)
    {
        return $this->persistForm($object, $parameters, 'PATCH', $flush, $validationGroup);
    }

    /**
     * Persist the Data by form.
     *
     * @param $object
     * @param array         $parameters
     * @param String        $method
     *
     * @return object
     *
     * @throws \Opengnsys\CoreBundle\Exception\InvalidFormException
     */
    public function persistForm($object, array $parameters, $method = "PUT", $flush = true, $validationGroup = null)
    {
        if($validationGroup == null){
            if($method === 'POST'){
                $validationGroup = 'creation';
            }else if($method === 'PUT' || $method === 'PATCH'){
                $validationGroup = 'edition';
            }else{
                $validationGroup = "";
            }
        }

        $object = $this->processForm($object, $parameters, 'PATCH', $validationGroup);

        if($validationGroup === 'creation'){
            $this->prePersist($object);
        }else if($validationGroup === 'edition'){
            $this->preUpdate($object);
        }

        $this->persist($object, $flush);

        if($validationGroup === 'creation'){
            $this->postPersist($object);
        }else if($validationGroup === 'edition'){
            $this->postUpdate($object);
        }

        return $object;
    }

    /**
     * Processes the form.
     *
     * @param $object
     * @param array         $parameters
     * @param String        $method
     *
     * @return object
     *
     * @throws \Opengnsys\CoreBundle\Exception\InvalidFormException
     */
    public function processForm($object, array $parameters, $method = "PUT", $validationGroup = "")
    {
        $this->preProccessForm($parameters, $method, $validationGroup);

        $this->form = $this->formFactory->create($this->formTypeClass, $object, array('method' => $method, 'validation_groups'=>$validationGroup, 'csrf_protection' => false, 'allow_extra_fields' => true,));
        $this->form->submit($parameters, 'PATCH' !== $method);
        if ($this->form->isValid()) {
            $object = $this->form->getData();
            return $object;
        }

        throw new InvalidFormException('Invalid submitted data', $this->form);
    }

    public function getForm(){
        return $this->form;
    }

    /**
     * {@inheritdoc}
     */
    public function preProccessForm(&$parameters, $method = "PUT", $validationGroup = "")
    {
    }
}