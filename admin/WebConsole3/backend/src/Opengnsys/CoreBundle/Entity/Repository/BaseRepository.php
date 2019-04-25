<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega Alcantara on 01/10/18. <miguelangel.devega@sic.uhu.es>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */

namespace Opengnsys\CoreBundle\Entity\Repository;

class BaseRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByObservable($term = "", $limit = null, $offset = null, $ordered = array(), $selects = array(), $searchs = array(), $matching = array())
    {
        $qb = $this->createQueryBuilder('o');

        $qb = $this->createSelect($qb, $selects);

        $qb = $this->createSearch($qb, $term, $searchs);

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

        $qb = $this->createSearch($qb, $term, $searchs);

        $qb = $this->createMaching($qb, $matching);

        $count = $qb->getQuery()->getSingleScalarResult();
        return $count;
    }

    public function countAll($matching = array())
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select("count(DISTINCT o.id)");

        $qb = $this->createMaching($qb, $matching);

        $count = $qb->getQuery()->getSingleScalarResult();
        return $count;
    }

    public function searchBy($matching = array(), $ordered = array(), $limit, $offset = 0){
        $qb = $this->createQueryBuilder('o');

        $qb = $this->createMaching($qb, $matching);

        if($limit != null){
            $qb->setMaxResults($limit);
        }

        if($offset){
            $qb->setFirstResult($offset);
        }

        $qb = $this->createOrdered($qb, $ordered);

        $objects = $qb->getQuery()->getResult();

        return $objects;

    }

    public function searchOneBy($matching = array()){
        $qb = $this->createQueryBuilder('o');

        $qb = $this->createMaching($qb, $matching);

        $object = $qb->getQuery()->getOneOrNullResult();

        return $object;

    }

    protected function createSelect($qb, $selects){
        if(count($selects) > 0){
            $select = "";
            foreach ($selects as $field) {
                if (\strpos($field, '.') === false) {
                    $field = "o.".$field;
                }
                $select .= $field.", ";
            }
            $select = substr($select, 0, -2);
            $qb->select("DISTINCT ".$select);
        }
        return $qb;
    }

    protected function createSearch($qb, $term, $searchs){
        if($term != "" && count($searchs) > 0){
            $search = "";
            foreach ($searchs as $field){
                if (\strpos($field, '.') === false) {
                    $field = "o.".$field;
                }
                $search .= $field." LIKE :term OR ";
            }
            $search = substr($search, 0, -3);
            $qb->andWhere($search)->setParameter('term', '%' . $term . '%');
        }
        return $qb;
    }

    protected function createOrdered($qb, $ordered){
        foreach ($ordered as $key => $value) {
            if (\strpos($key, '.') === false) {
                $key = "o.".$key;
            }
            $qb->addOrderBy($key, $value);
        }
        return $qb;
    }

    protected function createMaching($qb, $matching){
        $joins = [];
        $i=65;

        foreach ($matching as $key => $value) {
            $function = null;

            // Comprueba si es una where simple o con condicionantes
            if(!is_array($value)){
                $comparison = "=";
            }else{
                $item = $value;
                $comparison = $item["comparison"]; //operator
                $value = $item["value"];

                if($comparison == "LIKE"){
                    $value = '%' . $value . '%';
                }

                if(array_key_exists('function', $item)){
                    $function = $item["function"];
                }

            }

            // Comprueba si es un where con left-join o la propiedad esta en el objeto buscado
            if (\strpos($key, '.') === false) {
                $field = "o.".$key;
            }else{
                $keys = explode(".", $key);
                $letter = "o";
                foreach ($keys as $key){

                    if($key !== end($keys)) {
                        if (!array_key_exists($key, $joins)) {
                            $joins[$key] = $newLetter = chr($i++);
                            // First Element
                            if ($key === reset($keys)) {
                                $join = "o." . $key;
                            } else {
                                $join = $letter . "." . $key;

                            }
                            //echo "leftJoin: " . $join . "=" . $newLetter . "<br>";
                            $letter = $newLetter;
                            $qb->leftJoin($join, $letter);
                        } else {
                            $letter = $joins[$key];
                        }
                    }
                    // Last Element
                    else {
                        $field = $letter.".".$key;
                    }
                }
            }

            // Comprobamos si el parámetro está
            if($function !== null){
                $field = $function."(".$field.")";
            }

            if($comparison === "in"){
                $where = $field." ".$comparison." (:".$key.")";
            }else if($comparison === ">=" || $comparison === ">"){
                $key = $key."Major";
                $where = $field." ".$comparison." :".$key;
            }else if($comparison === "<=" || $comparison === "<"){
                $key = $key."Minor";
                $where = $field." ".$comparison." :".$key;
            }else{
                $where = $field." ".$comparison." :".$key;
            }

            //echo "andWhere: ".$where." --> ".$key." = ".$value."<br>";
            $qb->andWhere($where)->setParameter($key, $value);
        }
        return $qb;
    }
}
