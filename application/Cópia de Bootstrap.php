<?php
//require_once "Autoloader.php";
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
	public function __construct($application)
	{
		parent::__construct($application);
		$this->_loadComponents();
		$this->_connectDatabase();
	}
	
	private function _connectDatabase()
	{
		$resource = $this->getPluginResource('db');
		$db = $resource->getDbAdapter();
		Zend_Registry::set('db',$db);
	}
	
	private function _loadComponents()
	{
		require_once 'Zend/Loader/Autoloader.php';
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('Zend');
		$autoloader->registerNamespace('Extra');
		//$autoloader->pushAutoloader(array('Component_AutoLoader', 'autoload'));
	}

}

