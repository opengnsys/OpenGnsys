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
 * NetworkSettingsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NetworkSettingsRepository extends BaseRepository
{
    public function findByObservable($term = "", $limit = null, $offset = null, $ordered = array(), $selects = array(), $searchs = array(), $matching = array())
    {
        $qb = $this->createQueryBuilder('o');

        if(count($selects) > 0){
            $qb = $this->createSelect($qb, $selects);
        }else{
            $qb->select("DISTINCT o.createdAt, o.updatedAt, o.notes, o.proxy, o.dns, o.netmask, o.router, o.ntp, o.p2pTime, o.p2pMode, o.mcastIp, o.mcastSpeed, o.mcastPort, o.mcastMode, o.id");
        }

        if($term != ""){
            if(count($searchs) > 0){
                $qb = $this->createSearch($qb, $term, $searchs);
            }else{
                $qb->andWhere("o.createdAt LIKE :term OR o.updatedAt LIKE :term OR o.notes LIKE :term OR o.proxy LIKE :term OR o.dns LIKE :term OR o.netmask LIKE :term OR o.router LIKE :term OR o.ntp LIKE :term OR o.p2pTime LIKE :term OR o.p2pMode LIKE :term OR o.mcastIp LIKE :term OR o.mcastSpeed LIKE :term OR o.mcastPort LIKE :term OR o.mcastMode LIKE :term OR o.id LIKE :term ")->setParameter('term', '%' . $term . '%');
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
                $qb->andWhere("o.createdAt LIKE :term OR o.updatedAt LIKE :term OR o.notes LIKE :term OR o.proxy LIKE :term OR o.dns LIKE :term OR o.netmask LIKE :term OR o.router LIKE :term OR o.ntp LIKE :term OR o.p2pTime LIKE :term OR o.p2pMode LIKE :term OR o.mcastIp LIKE :term OR o.mcastSpeed LIKE :term OR o.mcastPort LIKE :term OR o.mcastMode LIKE :term OR o.id LIKE :term ")->setParameter('term', '%' . $term . '%');
            }
        }

        $qb = $this->createMaching($qb, $matching);

        $count = $qb->getQuery()->getSingleScalarResult();
        return $count;
    }
}
