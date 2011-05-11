<?php
/**  
 * 
 * 
 * @package    library
 * @subpackage Template
 * @since      18.10.2010 16:14:54
 * @author     enesterov
 * @category   none
 */

	namespace Template;
	
class MyDOMDocument {
		private $_delegate;
		private $_validationErrors;

		public function __construct (\DOMDocument $pDocument) {
			$this->_delegate = $pDocument;
			$this->_validationErrors = array();
		}

		public function __call ($pMethodName, $pArgs) {
			if ($pMethodName == "validate") {
				$eh = set_error_handler(array($this, "onValidateError"));
				$rv = $this->_delegate->validate();
				if ($eh) {
					set_error_handler($eh);
				}
				return $rv;
			}
			else {
				return call_user_func_array(array($this->_delegate, $pMethodName), $pArgs);
			}
		}
		public function __get ($pMemberName) {
			if ($pMemberName == "errors") {
				return $this->_validationErrors;
			}
			else {
				return $this->_delegate->$pMemberName;
			}
		}
		public function __set ($pMemberName, $pValue) {
			$this->_delegate->$pMemberName = $pValue;
		}
		public function onValidateError ($pNo, $pString, $pFile = null, $pLine = null, $pContext = null) {
			$this->_validationErrors[] = preg_replace("/^.+: */", "", $pString);
		}
	}

abstract class Template {
	public $xml;
	
	function __set($name="", $value = "")
	{
		//var_dump($name);
		return $this->$name = $this->xml->addChild( $name, $value );
	}
	
	public function __construct($data = "")
	{
		$child = get_called_class();
		
		$dtd = preg_replace("/Controllers\\C/", "", $child);
		$dtd = preg_replace("/Controller/", "", $dtd);
		$dtd = strtolower($dtd);
		
		$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE mvideo_xml SYSTEM \"http://".$_SERVER['HTTP_HOST']."/mobile/public/$dtd.dtd\">\n<mvideo_xml date=\"" . date("Y-m-d H:i:s") . "\">\n</mvideo_xml>";
		
		$this->xml = new \SimpleXMLElement($xmlstr);
		
	}
	
	public function __destruct()
	{
		$doc=$this->xml->asXML();
		
		
		//print preg_replace("/></", ">\n<", $doc);
		//try
		//{
			$dom = new \DOMDocument;
			$dom->loadXML($doc, LIBXML_DTDLOAD|LIBXML_DTDATTR);
			$myDoc = new MyDOMDocument($dom);
			$isValid = $myDoc->validate();
			/*if (!$isValid) 
			{
				throw new \MyDomException($myDoc->errors);
			}*/
			header('Content-type: text/xml; charset=utf-8');
			echo preg_replace("/></", ">\n<", $doc);
		/*}
		catch(\MyException $e)
		{
			echo $e;
		}*/
	}
	

}