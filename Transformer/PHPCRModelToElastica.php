<?php 
namespace RC\ElasticSearchPHPCRProviderBundle\Transformer;

use FOQ\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Symfony\Component\Form\Util\PropertyPath;
use RC\ElasticSearchPHPCRProviderBundle\Helper\NameHelper;

class PHPCRModelToElastica implements ModelToElasticaTransformerInterface
{
	protected $dm, $uow;
	
	protected $options = array(
			'identifier' => 'id'
	);

	public function __construct($dm){
		$this->dm = $dm;
		$this->uow = $dm->getUnitOfWork(); 
	}
	
	
	protected function doTransform($key, $mapping, $object, $property, $document, $identifier, $identifierProperty){
		
		if (!empty($mapping['_parent']) && $mapping['_parent'] !== '~') {
			$parent             = $property->getValue($object);
			$identifierProperty = new PropertyPath($mapping['_parent']['identifier']);
			$document->setParent($identifierProperty->getValue($parent));
		} else if (isset($mapping['type']) && in_array($mapping['type'], array('nested', 'object'))) {
			$submapping     = $mapping['properties'];
			$subcollection  = $property->getValue($object);
			$document->add($key, $this->transformNested($subcollection, $submapping, $document));
		} else if (isset($mapping['type']) && $mapping['type'] == 'attachment') {
			$attachment = $property->getValue($object);
			if ($attachment instanceof \SplFileInfo) {
				$document->addFile($key, $attachment->getPathName());
			} else {
				$document->addFileContent($key, $attachment);
			}
		} else {
			$document->add($key, $this->normalizeValue($property->getValue($object)));
		}
		
	}

	/**
	 * Transforms an object into an elastica object having the required keys
	 *
	 * @param object $object the object to convert
	 * @param array  $fields the keys we want to have in the returned array
	 *
	 * @return Elastica_Document
	 **/
	public function transform($object, array $fields)
	{
		$identifierProperty = new PropertyPath($this->options['identifier']);
		$identifier         = NameHelper::normalizeName($identifierProperty->getValue($object));
		$document           = new \Elastica_Document($identifier);
		
		foreach ($fields as $key => $mapping) {
			$property = new PropertyPath($key);
			
			if (isset($mapping['multilanguage']) && $mapping['multilanguage'] && is_array($mapping['languages']) && count($mapping['languages']) > 1 ){
				foreach($mapping['languages'] as $locale){
					$this->uow->doLoadTranslation($object, $this->dm->getClassMetadata(get_class($object)), $locale);
					$this->doTransform($key.'-'.$locale, $mapping, $object, $property, $document, $identifier, $identifierProperty);
				}
			}else{
				$this->doTransform($key, $mapping, $object, $property, $document, $identifier, $identifierProperty);
			}
				
				
		}
		return $document;

	}

	/**
	 * transform a nested document or an object property into an array of ElasticaDocument
	 *
	 * @param array $objects    the object to convert
	 * @param array $fields     the keys we want to have in the returned array
	 * @param Elastica_Document $parent the parent document
	 *
	 * @return array
	 */
	protected function transformNested($objects, array $fields, $parent)
	{
		if (is_array($objects) || $objects instanceof \Traversable || $objects instanceof \ArrayAccess) {
			$documents = array();
			foreach ($objects as $object) {
				$document = $this->transform($object, $fields);
				$documents[] = $document->getData();
			}

			return $documents;
		} elseif (null !== $objects) {
			$document = $this->transform($objects, $fields);

			return $document->getData();
		}

		return array();
	}

	/**
	 * Attempts to convert any type to a string or an array of strings
	 *
	 * @param mixed $value
	 *
	 * @return string|array
	 */
	protected function normalizeValue($value)
	{
		$normalizeValue = function(&$v)
		{
			if ($v instanceof \DateTime) {
				$v = $v->format('c');
			} elseif (!is_scalar($v) && !is_null($v)) {
				$v = (string)$v;
			}
		};

		if (is_array($value) || $value instanceof \Traversable || $value instanceof \ArrayAccess) {
			$value = is_array($value) ? $value : iterator_to_array($value);
			array_walk_recursive($value, $normalizeValue);
		} else {
			$normalizeValue($value);
		}

		return $value;
	}
}