<?php

namespace RC\ElasticSearchPHPCRProviderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('RCElasticSearchPHPCRProviderBundle:Default:index.html.twig', array('name' => $name));
    }
}
