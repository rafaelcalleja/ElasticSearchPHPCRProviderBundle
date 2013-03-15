<?php

namespace RC\ElasticSearchPHPCRProviderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	/** var Elastica_Type */
    	$userType = $this->container->get('foq_elastica.index.website.user');
    	
    	/** var Elastica_ResultSet */
    	$resultSet = $userType->search('inicio');
    	die(var_dump($resultSet));
    	
        return $this->render('RCElasticSearchPHPCRProviderBundle:Default:index.html.twig', array('name' => $name));
    }
    
    
}
