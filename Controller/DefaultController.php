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
    	$resultSet = $userType->search('test');
    	
    	$em = $this->get('doctrine')->getManager('wordpress');
    	$repo = $em->getRepository('HypebeastWordpressBundle:Post');
    	
    	$all = $repo->findAll();
    	
    	die(var_dump($resultSet));
    	
        return $this->render('RCElasticSearchPHPCRProviderBundle:Default:index.html.twig', array('name' => $name));
    }
    
    
}
