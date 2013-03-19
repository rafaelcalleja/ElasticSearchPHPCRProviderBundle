<?php
namespace RC\ElasticSearchPHPCRProviderBundle\Helper; 
class NameHelper{
	
	
	public static function normalizeName($name){
		return str_replace('/', '_', $name);
	}
}