<?php

namespace RC\ElasticSearchPHPCRProviderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;
use Symfony\Component\Yaml\Parser;
use FOQ\ElasticaBundle\DependencyInjection\FOQElasticaExtension ;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RCElasticSearchPHPCRProviderExtension extends FOQElasticaExtension implements PrependExtensionInterface
{
	//protected $loadedDrivers = array(); 
	
	protected function loadDriver(ContainerBuilder $container, $driver)
	{
		if (in_array($driver, $this->loadedDrivers)) {
			return;
		}
		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load($driver.'.xml');
		$this->loadedDrivers[] = $driver;
	}
	
	public function prepend(ContainerBuilder $container)
	{
		$foq_extension = $container->getExtension('foq_elastica');
		$configs = $container->getExtensionConfig($foq_extension->getAlias());
		//$configs[0]["indexes"]["website"]["types"]["user"]["persistence"]["driver"] = 'phpcr';
		
		//die(var_dump($foq_extension->setAlias()));	
		//$container->loadFromExtension('foq_elastica', $configs[0]);
		//$own = $container->getExtensionConfig($this->getAlias());
		
 		$container->prependExtensionConfig('rc_elastic_search_phpcr_provider', $configs[0]);
		//die(var_dump($container->getAliases(), $foq_extension->getAlias(), $foq_extension->getNamespace()));
		// ...
	}
	
	protected function array_merge_recursive_distinct ( array &$array1, array &$array2 )
	{
		$merged = $array1;
	
		foreach ( $array2 as $key => &$value )
		{
			if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
			{
				$merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
			}
			else
			{
				$merged [$key] = $value;
			}
		}
	
		return $merged;
	}
	
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
       
      

// 		$yaml = new Parser();
		
// 		$value = $yaml->parse(file_get_contents($container->getParameter('kernel.root_dir').'/config/elasticasearch.yml'));
// 		$value["foq_elastica"]["indexes"]["website"]["types"]["user"]["persistence"]["driver"] = 'phpcr';
// 		$value["foq_elastica"]["indexes"]["website"]["types"]["user"]["mappings"]["title"]["multilanguage"] = true;
    	
    	
		//$configelastica = array_values($value);
   // 	die(var_dump($configs));
//    		$add = $configs[1] + $configs[0];
   		
    	//die(var_dump( $configs[0] ));
    	if(empty($configs[1]) || empty($configs[0])) return false;
    	
   		$configelastica = array($this->array_merge_recursive_distinct( $configs[0], $configs[1] ));
//    		die(var_dump(array_values($value), $configelastica));
		//die(var_dump($configelastica));
    	//$configelastica = $configs[0];
		
		
		$configuration =  new Configuration($configelastica, $container);
		
		$processor     = new Processor();
		$config        = $processor->process($configuration->getConfigTree(), $configelastica);
		
		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('config.xml');
// 		var_dump($config);
		//$config = $configelastica;
		
// 		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
// 		$loader->load('config.xml');
		//$container->setAlias('foq_elastica', 'rc_elastic_search_phpcr_provider');
		
		if (empty($config['clients']) || empty($config['indexes'])) {
			throw new InvalidArgumentException('You must define at least one client and one index');
		}
		
		if (empty($config['default_client'])) {
			$keys = array_keys($config['clients']);
			$config['default_client'] = reset($keys);
		}
		
		if (empty($config['default_index'])) {
			$keys = array_keys($config['indexes']);
			$config['default_index'] = reset($keys);
		}
		
		$clientIdsByName = $this->loadClients($config['clients'], $container);
		$indexIdsByName  = $this->loadIndexes($config['indexes'], $container, $clientIdsByName, $config['default_client']);
		$indexRefsByName = array_map(function($id) {
			return new Reference($id);
		}, $indexIdsByName);
		
		$this->loadIndexManager($indexRefsByName, $container);
		$this->loadResetter($this->indexConfigs, $container);
		
		$container->setAlias('foq_elastica.client', sprintf('foq_elastica.client.%s', $config['default_client']));
		$container->setAlias('foq_elastica.index', sprintf('foq_elastica.index.%s', $config['default_index']));
		
		$this->createDefaultManagerAlias($config['default_manager'], $container);
		
		
// 		$configuration = new Configuration($configelastica);
// 		//$config = $this->processConfiguration($configuration, $configelastica);
		
// 		//$loader = new elasticloader();
// 		//$loader->load(array_values($value), $container);
// 		parent::load($configelastica, $container);
		

        
        

//         $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
//         $loader->load('services.xml');
//         $typeConfig = array('driver' => 'phpcr', 'model' => 'Application\UserBundle\Entity\User',
//         				'identifier' => 'id'
        		
//         		);
//         $typeConfig['elastica_to_model_transformer']['hydrate'] = true;
        
//         $this->loadDriver($container, $typeConfig['driver']);
        
//         $indexName = 'website';
//         $typeName = 'user';
        
//         $elasticaToModelTransformerId = $this->loadElasticaToModelTransformer($typeConfig, $container, $indexName, $typeName);
//         $modelToElasticaTransformerId = $this->loadModelToElasticaTransformer($typeConfig, $container, $indexName, $typeName);
//         $objectPersisterId            = $this->loadObjectPersister($typeConfig, $typeDef, $container, $indexName, $typeName, $modelToElasticaTransformerId);
        
//         if (isset($typeConfig['provider'])) {
//         	$this->loadTypeProvider($typeConfig, $container, $objectPersisterId, $typeDef, $indexName, $typeName);
//         }
//         if (isset($typeConfig['finder'])) {
//         	$this->loadTypeFinder($typeConfig, $container, $elasticaToModelTransformerId, $typeDef, $indexName, $typeName);
//         }
//         if (isset($typeConfig['listener'])) {
//         	$this->loadTypeListener($typeConfig, $container, $objectPersisterId, $typeDef, $indexName, $typeName);
//         }
        
    }
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
    	
    	return new Configuration($config);
    }
    
//     protected function loadDriver(ContainerBuilder $container, $driver)
//     {
//     	if (in_array($driver, $this->loadedDrivers)) {
//     		return;
//     	}
//     	$loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
//     	$loader->load($driver.'.xml');
//     	$this->loadedDrivers[] = $driver;
//     }
    
    
//     protected function loadElasticaToModelTransformer(array $typeConfig, ContainerBuilder $container, $indexName, $typeName)
//     {
//     	if (isset($typeConfig['elastica_to_model_transformer']['service'])) {
//     		return $typeConfig['elastica_to_model_transformer']['service'];
//     	}
//     	$abstractId = sprintf('foq_elastica.elastica_to_model_transformer.prototype.%s', $typeConfig['driver']);
//     	$serviceId = sprintf('foq_elastica.elastica_to_model_transformer.%s.%s', $indexName, $typeName);
//     	$serviceDef = new DefinitionDecorator($abstractId);
//     	$serviceDef->addTag('foq_elastica.elastica_to_model_transformer', array('type' => $typeName, 'index' => $indexName));
    
//     	// Doctrine has a mandatory service as first argument
//     	$argPos = ('propel' === $typeConfig['driver']) ? 0 : 1;
    
//     	$serviceDef->replaceArgument($argPos, $typeConfig['model']);
//     	$serviceDef->replaceArgument($argPos + 1, array(
//     			'identifier'    => $typeConfig['identifier'],
//     			'hydrate'       => $typeConfig['elastica_to_model_transformer']['hydrate']
//     	));
//     	$container->setDefinition($serviceId, $serviceDef);
    
//     	return $serviceId;
//     }
    
//     protected function loadModelToElasticaTransformer(array $typeConfig, ContainerBuilder $container, $indexName, $typeName)
//     {
//     	if (isset($typeConfig['model_to_elastica_transformer']['service'])) {
//     		return $typeConfig['model_to_elastica_transformer']['service'];
//     	}
//     	$abstractId = sprintf('foq_elastica.model_to_elastica_transformer.prototype.auto');
//     	$serviceId = sprintf('foq_elastica.model_to_elastica_transformer.%s.%s', $indexName, $typeName);
//     	$serviceDef = new DefinitionDecorator($abstractId);
//     	$serviceDef->replaceArgument(0, array(
//     			'identifier' => $typeConfig['identifier']
//     	));
//     	$container->setDefinition($serviceId, $serviceDef);
    
//     	return $serviceId;
//     }
    
//     protected function loadObjectPersister(array $typeConfig, Definition $typeDef, ContainerBuilder $container, $indexName, $typeName, $transformerId)
//     {
//     	$abstractId = sprintf('foq_elastica.object_persister.prototype');
//     	$serviceId = sprintf('foq_elastica.object_persister.%s.%s', $indexName, $typeName);
//     	$serviceDef = new DefinitionDecorator($abstractId);
//     	$serviceDef->replaceArgument(0, $typeDef);
//     	$serviceDef->replaceArgument(1, new Reference($transformerId));
//     	$serviceDef->replaceArgument(2, $typeConfig['model']);
//     	$serviceDef->replaceArgument(3, $this->typeFields[sprintf('%s/%s', $indexName, $typeName)]);
//     	$container->setDefinition($serviceId, $serviceDef);
    
//     	return $serviceId;
//     }
}
