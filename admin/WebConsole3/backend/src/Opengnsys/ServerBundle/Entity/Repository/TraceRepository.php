<?php

namespace Opengnsys\ServerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TraceRepository extends EntityRepository{
	
	public function searchBy($limit , $offset, $finished)
	{

		$qb = $this->createQueryBuilder('o');


		if($finished != null){
		    if($finished){
                $qb->andWhere("o.status is not null");
            }else{
                $qb->andWhere("o.status is null");
            }
        }
		
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