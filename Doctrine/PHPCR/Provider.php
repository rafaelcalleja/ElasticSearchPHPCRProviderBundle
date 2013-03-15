<?php

namespace RC\ElasticSearchPHPCRProviderBundle\Doctrine\PHPCR;

use Doctrine\ODM\PHPCR\Query\QueryBuilder;
use FOQ\ElasticaBundle\Doctrine\AbstractProvider;
use FOQ\ElasticaBundle\Exception\InvalidArgumentTypeException;

class Provider extends AbstractProvider
{
    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::countObjects()
     */
    protected function countObjects($queryBuilder)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\QueryBuilder');
        }

        /* Clone the query builder before altering its field selection and DQL,
         * lest we leave the query builder in a bad state for fetchSlice().
         */
        
        $qb = $queryBuilder;
        $ret = 
        $qb //->where($qb->expr()->neq('phpcr:class', 'Not Exist'))
        
        // Remove ordering for efficiency; it doesn't affect the count
        ->resetPart('orderBy')
        ->getQuery()->getResult();
       
      //  var_dump($ret->getSingleResult());
        return count($ret);

        /*return $qb
            ->select($qb->expr()->count($queryBuilder->getRootAlias()))
            // Remove ordering for efficiency; it doesn't affect the count
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();*/
    }

    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::fetchSlice()
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\QueryBuilder');
        }
       

        return $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        	->toArray();
    }

    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::createQueryBuilder()
     */
    protected function createQueryBuilder()
    {
        return $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            // ORM query builders require an alias argument
            ->{$this->options['query_builder_method']}('a');
    }
}
