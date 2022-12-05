<?php
/**
* Auto loader extends
*
* @author Demetrio Guilardi
*/
class Component_AutoLoader implements Zend_Loader_Autoloader_Interface{
    protected $_baseDir;
    protected $_prefix;
    protected $_prefixLen;

    /**
    * Construtor
    *
    * @param string $baseDir
    * @param string $prefix
    * @return void
    * @access public
    */
    public function __construct($baseDir, $prefix)
    {
        $this->_baseDir = $baseDir;
        $this->_prefix = $prefix;
        $this->_prefixLen = strlen($prefix);
    }

    /**
    * Autoload - tenta incluir as classes de acordo com include_path
    *
    * @param string $className
    * @access public
    * @return void
    */
    public function autoload($className){

	if(strpos($className,'Model') === 0){
		$className	= str_replace('Model','models',$className);
	}

        $classPath = (strpos($className,'Controller'))
        ? strtolower(substr($className,0,1)).str_replace('_','/Controller/',substr($className,1))
        : str_replace('_','/',$className);
        $inc = @include($classPath.'.php');

        if(!$inc){
            $classPath = str_replace('/','_',$className);
            $inc = include($classPath.'.php');
        }
    }
}
?>
