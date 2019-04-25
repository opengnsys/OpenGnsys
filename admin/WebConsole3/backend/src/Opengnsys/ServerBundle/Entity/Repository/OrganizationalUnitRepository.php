<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega Alcantara on 01/10/18. <miguelangel.devega@sic.uhu.es>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */

namespace Opengnsys\ServerBundle\Entity\Repository;

use Opengnsys\CoreBundle\Entity\Repository\BaseRepository;

/**
 * OrganizationalUnitRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrganizationalUnitRepository extends BaseRepository
{


    public function findByObservable($term = "", $limit = null, $offset = null, $ordered = array(), $selects = array(), $searchs = array(), $matching = array())
    {
        $qb = $this->createQueryBuilder('o');

        if(count($selects) > 0){
            $qb = $this->createSelect($qb, $selects);
        }else{
            $qb->select("DISTINCT o.createdAt, o.updatedAt, o.notes, o.name, o.description, o.comments, o.id");
        }

        if($term != ""){
            if(count($searchs) > 0){
                $qb = $this->createSearch($qb, $term, $searchs);
            }else{
                $qb->andWhere("o.createdAt LIKE :term OR o.updatedAt LIKE :term OR o.notes LIKE :term OR o.name LIKE :term OR o.description LIKE :term OR o.comments LIKE :term OR o.id LIKE :term ")->setParameter('term', '%' . $term . '%');
            }
        }

        $qb = $this->createMaching($qb, $matching);

        $qb = $this->createOrdered($qb, $ordered);

        if($limit != null){
            $qb->setMaxResults($limit);
        }

        if($offset){
            $qb->setFirstResult($offset);
        }

        $entities = $qb->getQuery()->getScalarResult();
        return $entities;
    }

    public function countFiltered($term = "", $searchs = array(), $matching = array())
    {
        $qb = $this->createQueryBuilder('o');

        $qb->select("count(DISTINCT o.id)");

        if($term != ""){
            if(count($searchs) > 0){
                $qb = $this->createSearch($qb, $term, $searchs);
            }else{
                $qb->andWhere("o.createdAt LIKE :term OR o.updatedAt LIKE :term OR o.notes LIKE :term OR o.name LIKE :term OR o.description LIKE :term OR o.comments LIKE :term OR o.id LIKE :term ")->setParameter('term', '%' . $term . '%');
            }
        }

        $qb = $this->createMaching($qb, $matching);

        $count = $qb->getQuery()->getSingleScalarResult();
        return $count;
    }


    /*
   public function allBy($limit , $offset, $hierarchical, $leaf)
   {

       $qb = $this->createQueryBuilder('o');



       if($limit != null){
           $qb->setMaxResults($limit);
       }
       if($offset != null){
           $qb->setFirstResult($offset);
       }

       try {
           $objects = $qb->getQuery()->getResult();
       } catch (NoResultException $e) {
           $message = sprintf('Unable to find an objects');
           throw new NotFoundHttpException($message, null, 0, $e);
       }
       return $objects;
   }
   */


    protected function createMaching($qb, $matching)
    {

        if($matching['hierarchical']){
            $qb->andWhere("o.parent is null");
        }else if($matching['leaf']){
            $qb->andWhere("o.children is empty");
        }

        unset($matching['hierarchical']);
        unset($matching['leaf']);
        $qb = parent::createMaching($qb, $matching);

        return $qb;
    }
}
