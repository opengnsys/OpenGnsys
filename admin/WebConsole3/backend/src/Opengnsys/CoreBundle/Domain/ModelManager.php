<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega Alcantara on 16/04/16. <miguelangel.devega@sic.uhu.es>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
namespace Opengnsys\CoreBundle\Domain;

use Doctrine\ORM\EntityManager;

class ModelManager
{
    const ID_SEPARATOR = ';';

    protected $entityManager;
    protected $repository;
    protected $objectClass;

  
    public function __construct(EntityManager $entityManager, $objectClass = null)
    {
        $this->entityManager = $entityManager;
        $this->objectClass = $objectClass;
        if($objectClass != null){
            $this->repository = $this->entityManager->getRepository($objectClass);
        }
    }

    public function getClass(){
        /*
        if (false !== strpos($this->objectClass, ':')) {
            $metadata = $this->entityManager->getClassMetadata($this->objectClass);
            $this->class = $metadata->getName();
        }
        */

        return $this->objectClass;
    }

    public function getRepository(){
        return $this->repository;
    }

    /**
     * Get structure medatada
     */
    public function getMetadata()
    {
        return $this->entityManager->getMetadataFactory()->getMetadataFor($this->objectClass);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields($class)
    {
        $metadata = $this->entityManager->getClassMetadata($class);

        return $metadata->getFieldNames();
    }

    /**
     * {@inheritdoc}
     */
    public function getModelInstance($class)
    {
        $r = new \ReflectionClass($class);
        if ($r->isAbstract()) {
            throw new \RuntimeException(sprintf('Cannot initialize abstract class: %s', $class));
        }

        return new $class();
    }

    /**
     * Get a object.
     *
     * @param mixed $id
     *
     * @return object
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Get a list of objects.
     *
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     *
     * @return array
     */
    public function searchBy($limit, $offset = 0, $matching = array(), $ordered = array())
    {
        //return $this->repository->findBy($matching, $ordered, $limit, $offset);
        return $this->repository->searchBy($matching, $ordered, $limit, $offset);
    }

    /**
     * Get a one object.
     *
     *
     * @return object
     */
    public function searchOneBy($matching = array())
    {
        //return $this->repository->findOneBy($matching);
        return $this->repository->searchOneBy($matching);
    }

    public function create()
    {
        return new $this->objectClass();
    }

    /**
     * Delete a object.
     *
     * @param $object
     * @param array         $parameters
     *
     * @return object
     */
    public function delete($object, $flush = true)
    {
        $this->preRemove($object);
        $this->entityManager->remove($object);
        if($flush){
            $this->entityManager->flush();
        }
        $this->postRemove($object);
    }

    /**
     * Persist a object.
     *
     * @param $object
     * @param array         $parameters
     *
     * @return object
     */
    public function persist($object, $flush = true)
    {
        $this->prePersist($object);
        $this->entityManager->persist($object);
        if($flush){
            $this->entityManager->flush();
        }
        $this->postPersist($object);
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove($object)
    {
    }
}