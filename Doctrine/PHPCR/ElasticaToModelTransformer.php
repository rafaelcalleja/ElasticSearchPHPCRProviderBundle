<?php

namespace RC\ElasticSearchPHPCRProviderBundle\Doctrine\PHPCR;

use FOQ\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;
use FOQ\ElasticaBundle\Doctrine\ORM\Elastica_Document;
use Doctrine\ODM\PHPCR\Query\Query;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{
	public function transform(array $elasticaObjects)
	{
		var_dump('asd');
		$ids = $highlights = array();
		foreach ($elasticaObjects as $elasticaObject) {
			$ids[] = $elasticaObject->getId();
			$highlights[$elasticaObject->getId()] = $elasticaObject->getHighlights();
		}
	
		$objects = $this->findByIdentifiers($ids, $this->options['hydrate']);
		if (count($objects) < count($elasticaObjects)) {
			throw new \RuntimeException('Cannot find corresponding Doctrine objects for all Elastica results.');
		};
	
		foreach ($objects as $object) {
			if ($object instanceof HighlightableModelInterface) {
				$object->setElasticHighlights($highlights[$object->getId()]);
			}
		}
	
		$identifierProperty =  new PropertyPath($this->options['identifier']);
	
		// sort objects in the order of ids
		$idPos = array_flip($ids);
		usort($objects, function($a, $b) use ($idPos, $identifierProperty)
		{
			return $idPos[$identifierProperty->getValue($a)] > $idPos[$identifierProperty->getValue($b)];
		});
	
		return $objects;
	}
	
    /**
     * Fetch objects for theses identifier values
     *
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, $hydrate)
    {
        if (empty($identifierValues)) {
            return array();
        }
        $hydrationMode = $hydrate ? Query::HYDRATE_DOCUMENT : Query::HYDRATE_PHPCR;
        $qb = $this->registry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            ->createQueryBuilder('o');
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb->where($qb->expr()->in('o.'.$this->options['identifier'], ':values'))
            ->setParameter('values', $identifierValues);

        return $qb->getQuery()->setHydrationMode($hydrationMode)->execute();
    }
}
