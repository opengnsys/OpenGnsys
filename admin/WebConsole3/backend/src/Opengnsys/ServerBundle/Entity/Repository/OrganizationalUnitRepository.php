<?php

namespace Opengnsys\ServerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrganizationalUnitRepository extends EntityRepository{
	
	public function allBy($limit , $offset, $hierarchical, $leaf)
	{

		$qb = $this->createQueryBuilder('o');

		if($hierarchical){
			$qb->andWhere("o.parent is null");
		}else if($leaf){
			$qb->andWhere("o.children is empty");
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
}