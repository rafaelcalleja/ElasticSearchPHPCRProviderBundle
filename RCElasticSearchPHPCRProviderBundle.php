<?php

namespace RC\ElasticSearchPHPCRProviderBundle;

use FOQ\ElasticaBundle\DependencyInjection\Compiler\RegisterProvidersPass;
use FOQ\ElasticaBundle\DependencyInjection\Compiler\TransformerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RCElasticSearchPHPCRProviderBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
	
		$container->addCompilerPass(new RegisterProvidersPass(), PassConfig::TYPE_BEFORE_REMOVING);
		$container->addCompilerPass(new TransformerPass());
	}
	
	public function getAlias()
	{
		die('managed');
		return array('AcmeFooBundle', 'AcmeBarBundle');
	}
}
