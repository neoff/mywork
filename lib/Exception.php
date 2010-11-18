<?php
/**  
 * exception  настройка ошибок
 * 
 * @package    config
 * @subpackage exceprion
 * @since      07.10.2010 11:35:21
 * @author     enesterov
 * @category   none
 */

	function err2exc($errno, $errstr, $errfile, $errline) {
		throw new MyException($errno, $errstr, $errfile, $errline);
	}
	
	#set_error_handler('err2exc', E_ALL & ~E_NOTICE & ~E_WARNING &~ E_USER_NOTICE | E_STRICT);
	#error_reporting(E_ALL | E_STRICT);
	
	//set_error_handler('err2exc', E_ALL);
	#error_reporting(E_ALL | E_STRICT);

class Singleton {
	protected static $instance;  // object instance
	protected static $finale;
	protected static $stacktraces;
	protected static $xml;
	
	private function __construct() 
	{
	}
	private function __clone() {}
 
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
			$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<errors/>";
			if (self::$xml === null) {
				self::$xml = new \SimpleXMLElement($xmlstr);
				self::$xml->addChild( "error","500" );
				self::$xml->addChild( "description","Internal Server Error" );
				self::$stacktraces = self::$xml->addChild( "stacktraces" );
			}
		}
		return self::$instance;
	}
 
	public function doAction() 
	{ 
		return self::$xml;
	}
	
	public function getStack() 
	{ 
		return $this::$stacktraces;
	}
	
	public function doFinal($xml) 
	{ 
		if (self::$finale === null) {
			self::$finale = $xml;
			
			self::$xml = $xml;
			header('Content-type: text/xml; charset=utf-8');
			echo preg_replace("/></", ">\n<", self::$xml->asXML());
			//exit();
		}
	}
	
}
	
	
class MyDomException extends Exception{
	public function __construct($e)
	{
		$this->dom = $e;
		parent::__construct();
		
		
		
		$single = Singleton::getInstance();
		$this->xml = $single->doAction();
		$this->stacktraces = $single->getStack();
		if(DEBUG)
		{
			$this->domeError();
			$this->__toString();
		}
		$single->doFinal($this->xml);
		exit();
	}
	public function __toString () 
	{
		$stacks = $this->stacktraces->addChild( "stacktrace" );
		$stacks->addChild("message", $this->getMessage());
		foreach ($this->getTrace() as $key => $val)
		{
			$args = "";
			foreach ($val['args'] as $vals)
			{
				if($vals=="" or $vals===false) $vals="false";
				$args .=" ".$vals;
			}
			
			$stack = $stacks->addChild("stack", $val['file']);
			$stack->addAttribute("value", $key);
			//$stack->addChild( 'file', $val['file']);
			$stack->addAttribute( 'line', $val['line']);
			$stack->addAttribute( 'class', $val['class'].$val['type'].$val['function']."($args)");
			//$stack->addAttribute( 'function', $val['function']);
			//$stack->addAttribute( 'args', $args);
		}
		return " ";
	}
	private function domeError()
	{
		$dom = $this->stacktraces->addChild( "dom" );
		foreach ($this->dom as $key => $val)
		{
			$stack = $dom->addChild("node", $val);
			$stack->addAttribute("value", $key);
		}
		
	}
}


class MyException extends Exception {
	public function __construct($errno, $errstr, $errfile, $errline) {
		parent::__construct($errstr, $errno);
		//print $this->getMessage();
		//print_r($this->getTrace());
		//print $this->__toString();
		$this->file = $errfile;
		$this->line = $errline;
		
		$single = Singleton::getInstance();
		$this->xml = $single->doAction();
		$this->stacktraces =  $single->getStack();
		if(DEBUG)
			$this->__toString();
		$single->doFinal($this->xml);
		exit();
	}
	public function __toString () 
	{
		$stacks = $this->stacktraces->addChild( "stacktrace" );
		$stacks->addChild("message", $this->getMessage());
		//print_r($this->getTrace());
		foreach ($this->getTrace() as $key => $val)
		{
			$args = "";
			foreach ($val['args'] as $vals)
			{
				if($vals=="" or $vals===false) $vals="false";
				$args .=" ".$vals;
			}
			$file = (array_key_exists("file", $val))?$val['file']:__FILE__;
			$stack = $stacks->addChild("stack", $file);
			
			$stack->addAttribute("value", $key);
			
			if(array_key_exists("line", $val))
				$stack->addAttribute( 'line', $val['line']);
			$class = "";
			if(array_key_exists("class", $val))
				$class .= $val['class'];
			if(array_key_exists("type", $val))
				$class .= $val['type'];
			if(array_key_exists("function", $val))
				$class .= $val['function'];
			$stack->addAttribute( 'class', $class."($args)");
		}
		return " ";
	}

}
