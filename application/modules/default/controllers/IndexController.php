<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		Zend_Loader::loadClass('UtilDAO');
		//Zend_Loader::loadClass('AlbumForm');
    }
	
    function preDispatch() 
    {
        
    }
        
    function indexAction(){
        
    }

}

