<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Opengnsys on 01/10/18. <info@globunet.com>
 * Copyright (c) 2015 Opengnsys Soluciones Tecnológicas, SL. All rights reserved.
 *
 */

namespace Opengnsys\ServerBundle\Entity\Repository;

use Opengnsys\CoreBundle\Entity\Repository\BaseRepository;

/**
 * ImageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImageRepository extends BaseRepository
{
    public function findByObservable($term = "", $limit = null, $offset = null, $ordered = array(), $selects = array(), $searchs = array(), $matching = array())
    {
        $qb = $this->createQueryBuilder('o');

        if(count($selects) > 0){
            $qb = $this->createSelect($qb, $selects);
        }else{
            $qb->select("DISTINCT o.createdAt, o.updatedAt, o.notes, o.canonicalName, o.description, o.comments, o.path, o.type, o.revision, o.partitionInfo, o.fileSize, o.id");
        }

        if($term != ""){
            if(count($searchs) > 0){
                $qb = $this->createSearch($qb, $term, $searchs);
            }else{
                $qb->andWhere("o.createdAt LIKE :term OR o.updatedAt LIKE :term OR o.notes LIKE :term OR o.canonicalName LIKE :term OR o.description LIKE :term OR o.comments LIKE :term OR o.path LIKE :term OR o.type LIKE :term OR o.revision LIKE :term OR o.partitionInfo LIKE :term OR o.fileSize LIKE :term OR o.id LIKE :term ")->setParameter('term', '%' . $term . '%');
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
                $qb->andWhere("o.createdAt LIKE :term OR o.updatedAt LIKE :term OR o.notes LIKE :term OR o.canonicalName LIKE :term OR o.description LIKE :term OR o.comments LIKE :term OR o.path LIKE :term OR o.type LIKE :term OR o.revision LIKE :term OR o.partitionInfo LIKE :term OR o.fileSize LIKE :term OR o.id LIKE :term ")->setParameter('term', '%' . $term . '%');
            }
        }

        $qb = $this->createMaching($qb, $matching);

        $count = $qb->getQuery()->getSingleScalarResult();
        return $count;
    }
}