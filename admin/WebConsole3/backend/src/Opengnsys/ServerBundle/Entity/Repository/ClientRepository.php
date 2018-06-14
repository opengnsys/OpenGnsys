<?php

namespace Opengnsys\ServerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClientRepository extends EntityRepository{
	
	public function allBy($limit , $offset, $organizationalUnit, $recursive)
	{

		$qb = $this->createQueryBuilder('o');

		if($organizationalUnit){
            $qb->andWhere("o.organizationalUnit = :organizationalUnit")->setParameter("organizationalUnit", $organizationalUnit);
        }

		/*
		if($hierarchical){
			$qb->andWhere("o.parent is null");
		}else if($leaf){
			$qb->andWhere("o.children is empty");
		}
		*/
		
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

    public function searchStatus($clients , $ou){
        $qb = $this->createQueryBuilder('o');

        $qb->select('o.id, o.status');

        if($ou){
            $qb->andWhere("o.organizationalUnit = :organizationalUnit")->setParameter("organizationalUnit", $ou);
        }

        if($clients){
            $qb->andWhere("o.id in (:clients)")->setParameter("clients", $clients);
        }

        try {
            $objects = $qb->getQuery()->getScalarResult();
        } catch (NoResultException $e) {
            $message = sprintf('Unable to find an objects');
            throw new NotFoundHttpException($message, null, 0, $e);
        }
        return $objects;
    }
}